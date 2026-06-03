// message-deletion.js - Real-time message deletion system

class MessageDeletionManager {
    constructor() {
        this.deletedMessages = new Set(); // Cache for locally deleted messages
        this.deletionCheckInterval = null;
        this.currentUserId = null;
        this.currentPeerId = null;
        this.pendingDeletions = new Map(); // Track pending deletions for retry
        this.wsConnection = null; // For future WebSocket implementation
        this.realtimeEnabled = false;
    }

    // Initialize the deletion manager
    init(userId) {
        this.currentUserId = userId;
        this.startDeletionPolling();
        this.setupRealtimeDeletion();
        console.log('Message deletion manager initialized for user:', userId);
    }

    // Setup real-time deletion using polling with short interval (for instant feeling)
    startDeletionPolling() {
        if (this.deletionCheckInterval) {
            clearInterval(this.deletionCheckInterval);
        }
        
        // Poll every 2 seconds for new deletions
        this.deletionCheckInterval = setInterval(() => {
            if (this.currentPeerId) {
                this.checkNewDeletions();
            }
        }, 2000);
    }

    // Check for new deletions from server
    async checkNewDeletions() {
        if (!this.currentUserId || !this.currentPeerId) return;
        
        try {
            const response = await fetch(`messages.php?action=get_deleted_messages&peer_id=${this.currentPeerId}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                for (const deletedMsg of data.data) {
                    if (!this.deletedMessages.has(deletedMsg.message_id)) {
                        this.deletedMessages.add(deletedMsg.message_id);
                        this.removeMessageFromUI(deletedMsg.message_id);
                        this.showDeletionNotification(deletedMsg.message_id, deletedMsg.deleted_by);
                    }
                }
            }
        } catch (error) {
            console.error('Failed to check deletions:', error);
        }
    }

    // Delete a message (instant deletion for both sides)
    async deleteMessage(messageId, deleteForEveryone = true) {
        if (!messageId) {
            console.error('No message ID provided');
            return false;
        }

        // Remove from UI immediately for better UX
        this.removeMessageFromUI(messageId);
        this.deletedMessages.add(messageId);

        // Show local deletion indicator
        this.showLocalDeletionIndicator(messageId);

        // Send deletion request to server
        try {
            const response = await fetch('messages.php?action=delete_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message_id: messageId,
                    delete_for_everyone: deleteForEveryone
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('Message deleted successfully:', messageId);
                
                // If deletion for everyone, trigger immediate sync for peer
                if (deleteForEveryone) {
                    this.triggerPeerDeletionSync(messageId);
                }
                
                return true;
            } else {
                // If failed, add back to retry queue
                console.error('Deletion failed:', data.error);
                this.pendingDeletions.set(messageId, {
                    messageId,
                    deleteForEveryone,
                    retryCount: 0,
                    timestamp: Date.now()
                });
                this.scheduleRetry();
                return false;
            }
        } catch (error) {
            console.error('Delete message error:', error);
            this.pendingDeletions.set(messageId, {
                messageId,
                deleteForEveryone,
                retryCount: 0,
                timestamp: Date.now()
            });
            this.scheduleRetry();
            return false;
        }
    }

    // Delete multiple messages at once
    async deleteMessages(messageIds, deleteForEveryone = true) {
        if (!messageIds || messageIds.length === 0) return false;

        // Remove from UI immediately
        for (const messageId of messageIds) {
            this.removeMessageFromUI(messageId);
            this.deletedMessages.add(messageId);
        }

        try {
            const response = await fetch('messages.php?action=delete_multiple_messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message_ids: messageIds,
                    delete_for_everyone: deleteForEveryone
                })
            });

            const data = await response.json();

            if (data.success) {
                if (deleteForEveryone && data.data?.deleted_count) {
                    console.log(`${data.data.deleted_count} messages deleted for everyone`);
                }
                return true;
            }
            return false;
        } catch (error) {
            console.error('Delete multiple messages error:', error);
            return false;
        }
    }

    // Delete entire conversation
    async deleteConversation(peerId, deleteForBoth = true) {
        if (!peerId) return false;

        try {
            const response = await fetch('messages.php?action=delete_conversation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    peer_id: peerId,
                    delete_for_both: deleteForBoth
                })
            });

            const data = await response.json();

            if (data.success) {
                // Clear UI messages
                this.clearConversationFromUI();
                showNotification('Conversation deleted successfully', 'success');
                
                // Trigger peer sync if deleting for both
                if (deleteForBoth) {
                    this.triggerPeerConversationDeletion(peerId);
                }
                
                return true;
            }
            return false;
        } catch (error) {
            console.error('Delete conversation error:', error);
            return false;
        }
    }

    // Remove message from UI
    removeMessageFromUI(messageId) {
        const messageElement = document.querySelector(`.msg-item[data-message-id="${messageId}"]`);
        if (messageElement) {
            // Animate removal
            messageElement.style.transition = 'opacity 0.2s ease';
            messageElement.style.opacity = '0';
            setTimeout(() => {
                messageElement.remove();
                this.reorderMessageIndices();
            }, 200);
        }
    }

    // Show local deletion indicator
    showLocalDeletionIndicator(messageId) {
        // You can show a temporary "Message deleted" indicator
        const messageElement = document.querySelector(`.msg-item[data-message-id="${messageId}"]`);
        if (messageElement) {
            const deletionText = document.createElement('div');
            deletionText.className = 'deletion-indicator';
            deletionText.textContent = '🗑️ Message deleted';
            deletionText.style.cssText = `
                font-size: 10px;
                color: var(--text-dim);
                margin-top: 4px;
                font-family: var(--font-mono);
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            messageElement.appendChild(deletionText);
            setTimeout(() => {
                deletionText.style.opacity = '1';
            }, 10);
        }
    }

    // Show deletion notification
    showDeletionNotification(messageId, deletedBy) {
        if (deletedBy != this.currentUserId) {
            showNotification('A message was deleted by the other user', 'info');
        }
    }

    // Trigger immediate sync for peer (using AJAX)
    async triggerPeerDeletionSync(messageId) {
        try {
            await fetch('messages.php?action=sync_deletion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message_id: messageId,
                    trigger_sync: true
                })
            });
        } catch (error) {
            console.error('Failed to trigger peer sync:', error);
        }
    }

    // Trigger conversation deletion sync for peer
    async triggerPeerConversationDeletion(peerId) {
        try {
            await fetch('messages.php?action=sync_conversation_deletion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    peer_id: peerId
                })
            });
        } catch (error) {
            console.error('Failed to trigger conversation deletion sync:', error);
        }
    }

    // Clear conversation from UI
    clearConversationFromUI() {
        const messagesList = document.getElementById('messages-list');
        if (messagesList) {
            messagesList.innerHTML = '';
        }
    }

    // Reorder message indices after removal
    reorderMessageIndices() {
        const messages = document.querySelectorAll('.msg-item');
        messages.forEach((msg, index) => {
            msg.setAttribute('data-index', index);
        });
    }

    // Retry failed deletions
    scheduleRetry() {
        setTimeout(() => {
            this.retryPendingDeletions();
        }, 5000);
    }

    async retryPendingDeletions() {
        const pending = Array.from(this.pendingDeletions.values());
        
        for (const deletion of pending) {
            if (deletion.retryCount >= 3) {
                this.pendingDeletions.delete(deletion.messageId);
                showNotification(`Failed to delete message ${deletion.messageId} after multiple attempts`, 'error');
                continue;
            }
            
            deletion.retryCount++;
            const success = await this.deleteMessage(deletion.messageId, deletion.deleteForEveryone);
            
            if (success) {
                this.pendingDeletions.delete(deletion.messageId);
            }
        }
    }

    // Setup real-time deletion using Server-Sent Events or WebSocket
    setupRealtimeDeletion() {
        // Check if browser supports EventSource
        if (typeof EventSource !== 'undefined') {
            try {
                const eventSource = new EventSource('messages.php?action=listen_deletions');
                
                eventSource.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    if (data.type === 'message_deleted' && !this.deletedMessages.has(data.message_id)) {
                        this.deletedMessages.add(data.message_id);
                        this.removeMessageFromUI(data.message_id);
                        showNotification('A message was deleted', 'info');
                    } else if (data.type === 'conversation_deleted') {
                        if (this.currentPeerId == data.peer_id) {
                            this.clearConversationFromUI();
                            showNotification('Conversation was deleted by the other user', 'info');
                        }
                    }
                };
                
                eventSource.onerror = (error) => {
                    console.error('EventSource error:', error);
                    eventSource.close();
                    // Fallback to polling
                };
            } catch (error) {
                console.error('Failed to setup EventSource:', error);
            }
        }
    }

    // Set current peer
    setCurrentPeer(peerId) {
        this.currentPeerId = peerId;
        // Clear deleted messages cache when changing peer
        this.deletedMessages.clear();
    }

    // Clean up
    destroy() {
        if (this.deletionCheckInterval) {
            clearInterval(this.deletionCheckInterval);
            this.deletionCheckInterval = null;
        }
        this.pendingDeletions.clear();
        this.deletedMessages.clear();
    }
}

// Add context menu for messages
class MessageContextMenu {
    constructor(deletionManager) {
        this.deletionManager = deletionManager;
        this.currentMenu = null;
        this.setupContextMenu();
    }

    setupContextMenu() {
        // Add context menu to messages container using event delegation
        const messagesList = document.getElementById('messages-list');
        
        if (messagesList) {
            messagesList.addEventListener('contextmenu', (e) => {
                // Find the message item
                let target = e.target;
                while (target && target !== messagesList) {
                    if (target.classList && target.classList.contains('msg-item')) {
                        e.preventDefault();
                        this.showContextMenu(e, target);
                        break;
                    }
                    target = target.parentElement;
                }
            });
        }

        // Close context menu on click outside
        document.addEventListener('click', () => {
            this.hideContextMenu();
        });
    }

    showContextMenu(event, messageElement) {
        this.hideContextMenu();
        
        const messageId = messageElement.getAttribute('data-message-id');
        const isMyMessage = messageElement.classList.contains('mine');
        
        // Create context menu
        const menu = document.createElement('div');
        menu.className = 'message-context-menu';
        menu.style.cssText = `
            position: fixed;
            background: var(--bg-overlay);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-panel);
            z-index: 1000;
            min-width: 180px;
            overflow: hidden;
        `;
        menu.style.left = `${event.clientX}px`;
        menu.style.top = `${event.clientY}px`;
        
        // Add menu items
        const deleteItem = document.createElement('div');
        deleteItem.className = 'context-menu-item';
        deleteItem.innerHTML = '🗑️ Delete for me';
        deleteItem.style.cssText = `
            padding: 10px 16px;
            cursor: pointer;
            font-family: var(--font-ui);
            font-size: 13px;
            transition: background 0.15s;
            color: var(--danger);
        `;
        deleteItem.onmouseenter = () => deleteItem.style.background = 'var(--bg-raised)';
        deleteItem.onmouseleave = () => deleteItem.style.background = 'transparent';
        deleteItem.onclick = () => {
            this.deletionManager.deleteMessage(messageId, false);
            this.hideContextMenu();
        };
        menu.appendChild(deleteItem);
        
        // Add "delete for everyone" option (only for own messages)
        if (isMyMessage) {
            const deleteForEveryoneItem = document.createElement('div');
            deleteForEveryoneItem.className = 'context-menu-item';
            deleteForEveryoneItem.innerHTML = '⚠️ Delete for everyone';
            deleteForEveryoneItem.style.cssText = `
                padding: 10px 16px;
                cursor: pointer;
                font-family: var(--font-ui);
                font-size: 13px;
                transition: background 0.15s;
                color: var(--danger);
                border-top: 1px solid var(--border);
            `;
            deleteForEveryoneItem.onmouseenter = () => deleteForEveryoneItem.style.background = 'var(--bg-raised)';
            deleteForEveryoneItem.onmouseleave = () => deleteForEveryoneItem.style.background = 'transparent';
            deleteForEveryoneItem.onclick = () => {
                this.deletionManager.deleteMessage(messageId, true);
                this.hideContextMenu();
            };
            menu.appendChild(deleteForEveryoneItem);
        }
        
        document.body.appendChild(menu);
        this.currentMenu = menu;
        
        // Adjust position if menu goes off screen
        const rect = menu.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            menu.style.left = `${window.innerWidth - rect.width - 10}px`;
        }
        if (rect.bottom > window.innerHeight) {
            menu.style.top = `${window.innerHeight - rect.height - 10}px`;
        }
    }

    hideContextMenu() {
        if (this.currentMenu) {
            this.currentMenu.remove();
            this.currentMenu = null;
        }
    }
}

// Add deletion button to each message (alternative to context menu)
function addDeleteButtonToMessage(messageElement, messageId, deletionManager, isMyMessage) {
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'message-delete-btn';
    deleteBtn.innerHTML = '🗑️';
    deleteBtn.title = 'Delete message';
    deleteBtn.style.cssText = `
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--bg-raised);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        width: 24px;
        height: 24px;
        font-size: 12px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s, border-color 0.2s;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    messageElement.style.position = 'relative';
    messageElement.addEventListener('mouseenter', () => deleteBtn.style.opacity = '1');
    messageElement.addEventListener('mouseleave', () => deleteBtn.style.opacity = '0');
    
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (confirm('Delete this message?')) {
            deletionManager.deleteMessage(messageId, isMyMessage);
        }
    });
    
    messageElement.appendChild(deleteBtn);
}

// Initialize deletion manager when user logs in
let deletionManager = null;
let contextMenu = null;

// Override the existing initializeChat function to include deletion manager
const originalInitializeChat = window.initializeChat || function() {};

window.initializeChat = function(userData) {
    // Initialize deletion manager
    if (deletionManager) {
        deletionManager.destroy();
    }
    
    deletionManager = new MessageDeletionManager();
    deletionManager.init(userData.user_id);
    
    // Initialize context menu
    contextMenu = new MessageContextMenu(deletionManager);
    
    // Call original function if exists
    if (typeof originalInitializeChat === 'function') {
        originalInitializeChat(userData);
    }
};

// Override selectChatUser function to update deletion manager
const originalSelectChatUser = window.selectChatUser || function() {};

window.selectChatUser = function(userId, username) {
    if (deletionManager) {
        deletionManager.setCurrentPeer(userId);
    }
    
    if (typeof originalSelectChatUser === 'function') {
        originalSelectChatUser(userId, username);
    }
};

// Export for use in other files
window.MessageDeletionManager = MessageDeletionManager;
window.MessageContextMenu = MessageContextMenu;