// crypto.js - Complete fixed E2EE implementation
class E2EECrypto {
    constructor() {
        this.privateKey = null;
        this.publicKey = null;
        this.peerPublicKeys = new Map();
        this.myUserId = null;
    }
    
    // Generate ECDH key pair (P-256)
    async generateKeyPair() {
        try {
            const keyPair = await window.crypto.subtle.generateKey(
                {
                    name: 'ECDH',
                    namedCurve: 'P-256'
                },
                true,
                ['deriveKey', 'deriveBits']
            );
            
            this.privateKey = keyPair.privateKey;
            this.publicKey = keyPair.publicKey;
            
            console.log('Key pair generated successfully');
            return this;
        } catch (error) {
            console.error('Key generation failed:', error);
            throw error;
        }
    }
    
    // Export public key as JWK string
    async exportPublicKey() {
        try {
            const jwk = await window.crypto.subtle.exportKey('jwk', this.publicKey);
            // Clean up the JWK for storage
            const cleanJwk = {
                kty: jwk.kty,
                crv: jwk.crv,
                x: jwk.x,
                y: jwk.y
            };
            return JSON.stringify(cleanJwk);
        } catch (error) {
            console.error('Export public key failed:', error);
            throw error;
        }
    }
    
    // Import public key from JWK
    async importPublicKey(publicKeyJwk) {
        try {
            let jwk;
            if (typeof publicKeyJwk === 'string') {
                jwk = JSON.parse(publicKeyJwk);
            } else {
                jwk = publicKeyJwk;
            }
            
            // Validate required fields
            if (!jwk.x || !jwk.y) {
                throw new Error('Invalid public key format: missing x or y coordinates');
            }
            
            const key = await window.crypto.subtle.importKey(
                'jwk',
                {
                    kty: 'EC',
                    crv: 'P-256',
                    x: jwk.x,
                    y: jwk.y,
                    ext: true
                },
                {
                    name: 'ECDH',
                    namedCurve: 'P-256'
                },
                true,
                []
            );
            
            return key;
        } catch (error) {
            console.error('Import public key failed:', error);
            throw error;
        }
    }
    
    // Set peer's public key
    async setPeerPublicKey(peerId, publicKeyJwk) {
        try {
            if (!publicKeyJwk || publicKeyJwk === 'null' || publicKeyJwk === '') {
                console.warn(`No public key provided for peer ${peerId}`);
                return false;
            }
            
            const peerKey = await this.importPublicKey(publicKeyJwk);
            this.peerPublicKeys.set(peerId, peerKey);
            console.log(`Successfully imported public key for peer ${peerId}`);
            return true;
        } catch (error) {
            console.error(`Failed to set peer key for ${peerId}:`, error);
            return false;
        }
    }
    
    // Get peer's public key
    getPeerPublicKey(peerId) {
        return this.peerPublicKeys.get(peerId);
    }
    
    // Generate ephemeral key pair for PFS
    async generateEphemeralKeyPair() {
        return await window.crypto.subtle.generateKey(
            {
                name: 'ECDH',
                namedCurve: 'P-256'
            },
            true,
            ['deriveKey', 'deriveBits']
        );
    }
    
    // Derive shared secret between two keys
    async deriveSharedSecretFromKeys(privateKey, publicKey) {
        const sharedSecret = await window.crypto.subtle.deriveBits(
            {
                name: 'ECDH',
                public: publicKey
            },
            privateKey,
            256
        );
        
        return sharedSecret;
    }
    
    // Derive AES key from shared secret
    async deriveAesKey(sharedSecret) {
        return await window.crypto.subtle.importKey(
            'raw',
            sharedSecret,
            'AES-GCM',
            false,
            ['encrypt', 'decrypt']
        );
    }
    
    // Encrypt message with ECDH + AES-GCM
    async encryptMessage(peerId, plaintext) {
        try {
            console.log(`Encrypting message for peer ${peerId}`);
            
            // Get peer's public key
            const peerKey = this.peerPublicKeys.get(peerId);
            if (!peerKey) {
                throw new Error(`No public key for peer ${peerId}. Please ensure peer has registered.`);
            }
            
            // Generate ephemeral key pair for this message (Perfect Forward Secrecy)
            const ephemeralKeyPair = await this.generateEphemeralKeyPair();
            const ephemeralPublicJwk = await window.crypto.subtle.exportKey('jwk', ephemeralKeyPair.publicKey);
            
            // Clean ephemeral public key
            const cleanEphemeralJwk = {
                kty: ephemeralPublicJwk.kty,
                crv: ephemeralPublicJwk.crv,
                x: ephemeralPublicJwk.x,
                y: ephemeralPublicJwk.y
            };
            
            // Derive shared secret using ephemeral private key and peer's public key
            const sharedSecret = await this.deriveSharedSecretFromKeys(
                ephemeralKeyPair.privateKey,
                peerKey
            );
            
            // Derive AES-GCM key from shared secret
            const aesKey = await this.deriveAesKey(sharedSecret);
            
            // Generate random IV (12 bytes for GCM - recommended)
            const iv = window.crypto.getRandomValues(new Uint8Array(12));
            
            // Encode and encrypt the message
            const encodedText = new TextEncoder().encode(plaintext);
            const encrypted = await window.crypto.subtle.encrypt(
                {
                    name: 'AES-GCM',
                    iv: iv,
                    tagLength: 128
                },
                aesKey,
                encodedText
            );
            
            console.log(`Message encrypted successfully`);
            
            return {
                ciphertext: this.arrayBufferToBase64(encrypted),
                nonce: this.arrayBufferToBase64(iv),
                ephemeralKey: JSON.stringify(cleanEphemeralJwk)
            };
        } catch (error) {
            console.error('Encryption failed:', error);
            throw error;
        }
    }
    
    // Decrypt message
    async decryptMessage(senderId, ciphertextBase64, nonceBase64, ephemeralPublicJwk) {
        try {
            console.log(`Decrypting message from sender ${senderId}`);
            
            // Parse the ephemeral public key
            let ephemeralPublicJwkParsed;
            if (typeof ephemeralPublicJwk === 'string') {
                ephemeralPublicJwkParsed = JSON.parse(ephemeralPublicJwk);
            } else {
                ephemeralPublicJwkParsed = ephemeralPublicJwk;
            }
            
            // Validate ephemeral key
            if (!ephemeralPublicJwkParsed.x || !ephemeralPublicJwkParsed.y) {
                throw new Error('Invalid ephemeral key format');
            }
            
            // Import ephemeral public key from sender
            const ephemeralPublic = await window.crypto.subtle.importKey(
                'jwk',
                {
                    kty: 'EC',
                    crv: 'P-256',
                    x: ephemeralPublicJwkParsed.x,
                    y: ephemeralPublicJwkParsed.y,
                    ext: true
                },
                {
                    name: 'ECDH',
                    namedCurve: 'P-256'
                },
                false,
                []
            );
            
            // Derive shared secret using our private key and sender's ephemeral public key
            const sharedSecret = await this.deriveSharedSecretFromKeys(
                this.privateKey,
                ephemeralPublic
            );
            
            // Derive AES key from shared secret
            const aesKey = await this.deriveAesKey(sharedSecret);
            
            // Convert from Base64
            const ciphertext = this.base64ToArrayBuffer(ciphertextBase64);
            const iv = this.base64ToArrayBuffer(nonceBase64);
            
            // Decrypt
            const decrypted = await window.crypto.subtle.decrypt(
                {
                    name: 'AES-GCM',
                    iv: iv,
                    tagLength: 128
                },
                aesKey,
                ciphertext
            );
            
            const decryptedText = new TextDecoder().decode(decrypted);
            console.log(`Message decrypted successfully`);
            
            return decryptedText;
        } catch (error) {
            console.error('Decryption failed:', error);
            console.error('Debug info:', {
                senderId,
                ciphertextLength: ciphertextBase64?.length,
                nonceLength: nonceBase64?.length,
                ephemeralKeyLength: ephemeralPublicJwk?.length
            });
            throw error;
        }
    }
    
    // Encrypt file
    async encryptFile(peerId, file) {
        try {
            console.log(`Encrypting file for peer ${peerId}`);
            
            const ephemeralKeyPair = await this.generateEphemeralKeyPair();
            const ephemeralPublicJwk = await window.crypto.subtle.exportKey('jwk', ephemeralKeyPair.publicKey);
            
            const cleanEphemeralJwk = {
                kty: ephemeralPublicJwk.kty,
                crv: ephemeralPublicJwk.crv,
                x: ephemeralPublicJwk.x,
                y: ephemeralPublicJwk.y
            };
            
            const peerKey = this.peerPublicKeys.get(peerId);
            if (!peerKey) {
                throw new Error(`No public key for peer ${peerId}`);
            }
            
            const sharedSecret = await this.deriveSharedSecretFromKeys(
                ephemeralKeyPair.privateKey,
                peerKey
            );
            
            const aesKey = await this.deriveAesKey(sharedSecret);
            const iv = window.crypto.getRandomValues(new Uint8Array(12));
            
            // Read file as ArrayBuffer
            const fileBuffer = await file.arrayBuffer();
            
            const encrypted = await window.crypto.subtle.encrypt(
                {
                    name: 'AES-GCM',
                    iv: iv,
                    tagLength: 128
                },
                aesKey,
                fileBuffer
            );
            
            console.log(`File encrypted successfully`);
            
            return {
                encryptedData: encrypted,
                nonce: this.arrayBufferToBase64(iv),
                ephemeralKey: JSON.stringify(cleanEphemeralJwk)
            };
        } catch (error) {
            console.error('File encryption failed:', error);
            throw error;
        }
    }
    
    // Decrypt file
    async decryptFile(peerId, encryptedData, nonceBase64, ephemeralPublicJwk) {
        try {
            console.log(`Decrypting file from peer ${peerId}`);
            
            let ephemeralPublicJwkParsed;
            if (typeof ephemeralPublicJwk === 'string') {
                ephemeralPublicJwkParsed = JSON.parse(ephemeralPublicJwk);
            } else {
                ephemeralPublicJwkParsed = ephemeralPublicJwk;
            }
            
            const ephemeralPublic = await window.crypto.subtle.importKey(
                'jwk',
                {
                    kty: 'EC',
                    crv: 'P-256',
                    x: ephemeralPublicJwkParsed.x,
                    y: ephemeralPublicJwkParsed.y,
                    ext: true
                },
                {
                    name: 'ECDH',
                    namedCurve: 'P-256'
                },
                false,
                []
            );
            
            const sharedSecret = await this.deriveSharedSecretFromKeys(
                this.privateKey,
                ephemeralPublic
            );
            
            const aesKey = await this.deriveAesKey(sharedSecret);
            const iv = this.base64ToArrayBuffer(nonceBase64);
            
            let encryptedBuffer;
            if (encryptedData instanceof ArrayBuffer) {
                encryptedBuffer = encryptedData;
            } else if (typeof encryptedData === 'string') {
                encryptedBuffer = this.base64ToArrayBuffer(encryptedData);
            } else {
                encryptedBuffer = encryptedData;
            }
            
            const decrypted = await window.crypto.subtle.decrypt(
                {
                    name: 'AES-GCM',
                    iv: iv,
                    tagLength: 128
                },
                aesKey,
                encryptedBuffer
            );
            
            console.log(`File decrypted successfully`);
            return decrypted;
        } catch (error) {
            console.error('File decryption failed:', error);
            throw error;
        }
    }
    
    // Test if we can encrypt and decrypt with a peer
    async testKeyExchange(peerId) {
        try {
            console.log(`Testing key exchange with peer ${peerId}`);
            
            // Check if we have peer's public key
            const peerKey = this.peerPublicKeys.get(peerId);
            if (!peerKey) {
                console.error(`No public key for peer ${peerId}`);
                return false;
            }
            
            // Create a test message
            const testMessage = "KeyExchangeTest_" + Date.now();
            console.log(`Test message: ${testMessage}`);
            
            // Encrypt the test message
            const encrypted = await this.encryptMessage(peerId, testMessage);
            
            // Decrypt the test message
            const decrypted = await this.decryptMessage(
                peerId,
                encrypted.ciphertext,
                encrypted.nonce,
                encrypted.ephemeralKey
            );
            
            // Verify
            const success = (decrypted === testMessage);
            console.log(`Key exchange test ${success ? 'PASSED' : 'FAILED'}`);
            
            return success;
        } catch (error) {
            console.error('Key exchange test failed:', error);
            return false;
        }
    }
    
    // Verify that we can encrypt for ourselves (self-test)
    async selfTest() {
        try {
            console.log('Running self-test...');
            
            // Generate a test key pair
            const testKeyPair = await window.crypto.subtle.generateKey(
                {
                    name: 'ECDH',
                    namedCurve: 'P-256'
                },
                true,
                ['deriveKey', 'deriveBits']
            );
            
            const testPublicJwk = await window.crypto.subtle.exportKey('jwk', testKeyPair.publicKey);
            const cleanTestJwk = {
                kty: testPublicJwk.kty,
                crv: testPublicJwk.crv,
                x: testPublicJwk.x,
                y: testPublicJwk.y
            };
            
            // Import the test public key
            const importedKey = await this.importPublicKey(cleanTestJwk);
            
            // Generate ephemeral key
            const ephemeralKeyPair = await this.generateEphemeralKeyPair();
            const ephemeralPublicJwk = await window.crypto.subtle.exportKey('jwk', ephemeralKeyPair.publicKey);
            
            // Derive shared secret
            const sharedSecret = await this.deriveSharedSecretFromKeys(
                ephemeralKeyPair.privateKey,
                importedKey
            );
            
            const aesKey = await this.deriveAesKey(sharedSecret);
            
            // Test encryption/decryption
            const iv = window.crypto.getRandomValues(new Uint8Array(12));
            const testText = "SelfTest_" + Date.now();
            const encoded = new TextEncoder().encode(testText);
            
            const encrypted = await window.crypto.subtle.encrypt(
                {
                    name: 'AES-GCM',
                    iv: iv,
                    tagLength: 128
                },
                aesKey,
                encoded
            );
            
            const decrypted = await window.crypto.subtle.decrypt(
                {
                    name: 'AES-GCM',
                    iv: iv,
                    tagLength: 128
                },
                aesKey,
                encrypted
            );
            
            const decryptedText = new TextDecoder().decode(decrypted);
            const success = (decryptedText === testText);
            
            console.log(`Self-test ${success ? 'PASSED' : 'FAILED'}`);
            return success;
        } catch (error) {
            console.error('Self-test failed:', error);
            return false;
        }
    }
    
    // Utility: ArrayBuffer to Base64
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }
    
    // Utility: Base64 to ArrayBuffer
    base64ToArrayBuffer(base64) {
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }
}

// Initialize global crypto instance
window.e2eCrypto = null;