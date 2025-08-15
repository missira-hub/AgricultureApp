<template>
  <div class="chat-view">
    <!-- Left Sidebar: Conversations List -->
    <div class="conversations-sidebar">
      <div class="conversations-header">
        <div class="header-top">
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input 
              type="text" 
              placeholder="Search chats" 
              class="search-input"
              v-model="searchQuery"
            >
          </div>
        </div>
        <div class="chats-title">
          <h2>Chats</h2>
          <div class="header-actions">
            <button class="icon-btn">
              <i class="fas fa-comment"></i>
            </button>
            <button class="icon-btn" @click="showNewChatModal = true">
              <i class="fas fa-ellipsis-h"></i>
            </button>
          </div>
        </div>
      </div>
      
      <div class="conversations-list">
        <div 
          v-for="conversation in filteredConversations" 
          :key="conversation.id"
          @click="selectConversation(conversation.id)"
          :class="['conversation-item', { active: selectedConversationId === conversation.id }]"
        >
          <div class="conversation-avatar">
            <img 
              v-if="conversation.with_farmer?.avatar" 
              :src="conversation.with_farmer.avatar" 
              :alt="conversation.with_farmer.name"
            >
            <div v-else class="avatar-placeholder">
              {{ getInitials(conversation.with_farmer?.name) }}
            </div>
            <div class="status-indicator online"></div>
          </div>
          <div class="conversation-details">
            <div class="conversation-header">
              <div class="conversation-name">
                {{ conversation.with_farmer?.name || 'Unknown User' }}
              </div>
              <div class="conversation-time">
                {{ formatTime(conversation.last_message_at) }}
              </div>
            </div>
            <div class="conversation-last-message">
              {{ conversation.last_message?.text || 'No messages yet' }}
            </div>
          </div>
          <div v-if="conversation.unread_count" class="unread-badge">
            {{ conversation.unread_count }}
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Chat Messages -->
    <div class="chat-area">
      <div v-if="selectedConversationId" class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
          <div class="chat-user-info">
            <div class="chat-avatar">
              <img 
                v-if="selectedConversation?.with_farmer?.avatar" 
                :src="selectedConversation.with_farmer.avatar" 
                :alt="selectedConversation.with_farmer.name"
              >
              <div v-else class="avatar-placeholder">
                {{ getInitials(selectedConversation?.with_farmer?.name) }}
              </div>
              <div class="status-indicator online"></div>
            </div>
            <div class="chat-user-details">
              <div class="chat-user-name">
                {{ selectedConversation?.with_farmer?.name || 'Chat' }}
              </div>
              <div class="chat-user-status">Online</div>
            </div>
          </div>
          <div class="chat-actions">
            <button class="icon-btn">
              <i class="fas fa-phone"></i>
            </button>
            <button class="icon-btn">
              <i class="fas fa-video"></i>
            </button>
            <button class="icon-btn">
              <i class="fas fa-info-circle"></i>
            </button>
          </div>
        </div>

        <!-- Messages Area -->
        <div class="messages-area" ref="messagesContainer">
          <div 
            v-for="message in messages" 
            :key="message.id"
            :class="['message-group', { 'own-message': message.sender_id === currentUserId }]"
          >
            <div v-if="message.sender_id !== currentUserId" class="message-avatar">
              <img 
                v-if="selectedConversation?.with_farmer?.avatar" 
                :src="selectedConversation.with_farmer.avatar" 
                :alt="selectedConversation.with_farmer.name"
              >
              <div v-else class="avatar-placeholder small">
                {{ getInitials(selectedConversation?.with_farmer?.name) }}
              </div>
            </div>
            <div class="message-content">
              <div v-if="message.sender_id !== currentUserId" class="message-sender">
                {{ message.sender_name }}
              </div>
              <div class="message-bubble">
                <div class="message-text">{{ message.message }}</div>
              </div>
              <div class="message-time">
                {{ formatMessageTime(message.created_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="message-input-area">
          <div class="message-input-container">
            <button class="attachment-btn">
              <i class="fas fa-paperclip"></i>
            </button>
            <div class="input-wrapper">
              <textarea
                v-model="newMessage"
                @keydown.enter.prevent="sendMessage"
                placeholder="Type a new message..."
                class="message-input"
                rows="1"
              ></textarea>
            </div>
            <button class="voice-btn">
              <i class="fas fa-microphone"></i>
            </button>
            <button class="emoji-btn">
              <i class="fas fa-smile"></i>
            </button>
            <button @click="sendMessage" :disabled="!newMessage.trim()" class="send-btn">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- No conversation selected -->
      <div v-else class="no-conversation-selected">
        <div class="welcome-content">
          <i class="fas fa-comments"></i>
          <h3>Welcome to Messages</h3>
          <p>Select a conversation to start messaging</p>
        </div>
      </div>
    </div>

    <!-- Recently Added Sidebar -->
    <div class="recently-added-sidebar">
      <div class="sidebar-section">
        <div class="section-header">
          <i class="fas fa-plus-circle"></i>
          <span>Recently added</span>
        </div>
        <div class="user-list">
          <div v-for="user in recentlyAdded" :key="user.id" class="user-item">
            <div class="user-avatar">
              <img v-if="user.avatar" :src="user.avatar" :alt="user.name">
              <div v-else class="avatar-placeholder small">{{ getInitials(user.name) }}</div>
            </div>
            <span class="user-name">{{ user.name }}</span>
          </div>
        </div>
      </div>

      <div class="sidebar-section">
        <div class="section-header">
          <i class="fas fa-star"></i>
          <span>Top Rated</span>
        </div>
        <div class="user-list">
          <div v-for="user in topRated" :key="user.id" class="user-item">
            <div class="user-avatar">
              <img v-if="user.avatar" :src="user.avatar" :alt="user.name">
              <div v-else class="avatar-placeholder small">{{ getInitials(user.name) }}</div>
            </div>
            <span class="user-name">{{ user.name }}</span>
          </div>
        </div>
      </div>

      <div class="sidebar-section">
        <div class="section-header">
          <i class="fas fa-lightbulb"></i>
          <span>Suggestions</span>
        </div>
        <div class="user-list">
          <div v-for="user in suggestions" :key="user.id" class="user-item">
            <div class="user-avatar">
              <img v-if="user.avatar" :src="user.avatar" :alt="user.name">
              <div v-else class="avatar-placeholder small">{{ getInitials(user.name) }}</div>
            </div>
            <span class="user-name">{{ user.name }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- New Chat Modal -->
    <div v-if="showNewChatModal" class="modal-overlay" @click="showNewChatModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Start New Conversation</h3>
          <button @click="showNewChatModal = false" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Select User:</label>
            <select v-model="newChatUserId" class="form-select">
              <option value="">Choose a user...</option>
              <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Subject (optional):</label>
            <input 
              v-model="newChatSubject" 
              type="text" 
              placeholder="Enter subject..."
              class="form-input"
            >
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showNewChatModal = false" class="btn-secondary">Cancel</button>
          <button @click="startNewChat" :disabled="!newChatUserId" class="btn-primary">
            Start Chat
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatView',
  props: {
    currentUserId: {
      type: Number,
      required: false,
      default: null
    }
  },
  data() {
    return {
      conversations: [],
      messages: [],
      selectedConversationId: null,
      selectedConversation: null,
      newMessage: '',
      showNewChatModal: false,
      newChatUserId: '',
      newChatSubject: '',
      availableUsers: [],
      searchQuery: '',
      loading: false,
      recentlyAdded: [
        { id: 1, name: 'Philip Diaz', avatar: null },
        { id: 2, name: 'Anthony Bailey', avatar: null },
        { id: 3, name: 'Elizabeth Rivera', avatar: null },
        { id: 4, name: 'Francine Barrett', avatar: null },
        { id: 5, name: 'Patrick Rivera', avatar: null },
        { id: 6, name: 'Steve Meyer', avatar: null },
        { id: 7, name: 'Steve Pearson', avatar: null },
        { id: 8, name: 'Marie Ellis', avatar: null }
      ],
      topRated: [
        { id: 9, name: 'Patrick Rivera', avatar: null },
        { id: 10, name: 'Steve Meyer', avatar: null },
        { id: 11, name: 'Marie Ellis', avatar: null }
      ],
      suggestions: [
        { id: 12, name: 'Francine Barrett', avatar: null },
        { id: 13, name: 'Patrick Rivera', avatar: null },
        { id: 14, name: 'Steve Meyer', avatar: null },
        { id: 15, name: 'Steve Pearson', avatar: null },
        { id: 16, name: 'Marie Ellis', avatar: null }
      ]
    }
  },
  computed: {
    filteredConversations() {
      if (!this.searchQuery) return this.conversations;
      
      return this.conversations.filter(conv => 
        conv.with_farmer?.name?.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        conv.last_message?.text?.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  },
  mounted() {
    if (this.currentUserId) {
      this.fetchConversations();
      this.fetchAvailableUsers();
    }
  },
  watch: {
    currentUserId(newVal) {
      if (newVal) {
        this.fetchConversations();
        this.fetchAvailableUsers();
      }
    }
  },
  methods: {
    async fetchConversations() {
      if (!this.currentUserId) return;
      
      try {
        const response = await fetch('/api/conversations', {
          headers: {
            'Authorization': `Bearer ${this.getAuthToken()}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.ok) {
          this.conversations = await response.json();
        }
      } catch (error) {
        console.error('Error fetching conversations:', error);
      }
    },

    async selectConversation(conversationId) {
      this.selectedConversationId = conversationId;
      this.selectedConversation = this.conversations.find(c => c.id === conversationId);
      await this.fetchMessages(conversationId);
      this.markAsRead(conversationId);
    },

    async fetchMessages(conversationId) {
      try {
        const response = await fetch(`/api/conversations/${conversationId}/messages`, {
          headers: {
            'Authorization': `Bearer ${this.getAuthToken()}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.ok) {
          this.messages = await response.json();
          this.$nextTick(() => {
            this.scrollToBottom();
          });
        }
      } catch (error) {
        console.error('Error fetching messages:', error);
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || !this.selectedConversationId) return;

      const messageText = this.newMessage.trim();
      this.newMessage = '';

      try {
        const response = await fetch(`/api/conversations/${this.selectedConversationId}/messages`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.getAuthToken()}`,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            message: messageText
          })
        });

        if (response.ok) {
          const newMessage = await response.json();
          this.messages.push(newMessage);
          this.$nextTick(() => {
            this.scrollToBottom();
          });
          this.fetchConversations();
        }
      } catch (error) {
        console.error('Error sending message:', error);
        this.newMessage = messageText;
      }
    },

    async startNewChat() {
      if (!this.newChatUserId) return;

      try {
        const response = await fetch('/api/conversations/start', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.getAuthToken()}`,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            user_id: this.newChatUserId,
            subject: this.newChatSubject
          })
        });

        if (response.ok) {
          const conversation = await response.json();
          this.showNewChatModal = false;
          this.newChatUserId = '';
          this.newChatSubject = '';
          
          await this.fetchConversations();
          this.selectConversation(conversation.id);
        }
      } catch (error) {
        console.error('Error starting new chat:', error);
      }
    },

    async markAsRead(conversationId) {
      try {
        await fetch(`/api/conversations/${conversationId}/read`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.getAuthToken()}`,
            'Accept': 'application/json'
          }
        });
      } catch (error) {
        console.error('Error marking as read:', error);
      }
    },

    async fetchAvailableUsers() {
      if (!this.currentUserId) return;
      
      try {
        const response = await fetch('/api/users', {
          headers: {
            'Authorization': `Bearer ${this.getAuthToken()}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.ok) {
          this.availableUsers = await response.json();
        }
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },

    scrollToBottom() {
      if (this.$refs.messagesContainer) {
        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
      }
    },

    formatTime(timestamp) {
      if (!timestamp) return '';
      
      const date = new Date(timestamp);
      const now = new Date();
      const diff = now - date;
      
      if (diff < 24 * 60 * 60 * 1000) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      }
      
      if (diff < 7 * 24 * 60 * 60 * 1000) {
        return date.toLocaleDateString([], { weekday: 'short' });
      }
      
      return date.toLocaleDateString();
    },

    formatMessageTime(timestamp) {
      if (!timestamp) return '';
      const date = new Date(timestamp);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    getInitials(name) {
      if (!name) return 'U';
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    },

    getAuthToken() {
      return localStorage.getItem('auth_token') || 
             this.$store?.getters?.authToken || 
             '';
    }
  }
}
</script>

<style scoped>
.chat-view {
  display: flex;
  height: 100%;
  background: #f5f5f5;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Left Sidebar */
.conversations-sidebar {
  width: 320px;
  background: white;
  border-right: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
}

.conversations-header {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.header-top {
  margin-bottom: 16px;
}

.search-container {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9e9e9e;
  font-size: 14px;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  background: #f5f5f5;
  border: none;
  border-radius: 20px;
  font-size: 14px;
  outline: none;
}

.search-input:focus {
  background: #eeeeee;
}

.chats-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chats-title h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: #f5f5f5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #666;
  transition: all 0.2s;
}

.icon-btn:hover {
  background: #e0e0e0;
}

.conversations-list {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
}

.conversation-item:hover {
  background: #f8f9fa;
}

.conversation-item.active {
  background: #e3f2fd;
}

.conversation-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: #2196f3;
}

.conversation-avatar {
  position: relative;
  margin-right: 12px;
}

.conversation-avatar img,
.avatar-placeholder {
  width: 48px;
  height: 48px;
  border-radius: 50%;
}

.avatar-placeholder {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
}

.avatar-placeholder.small {
  width: 32px;
  height: 32px;
  font-size: 12px;
}

.status-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
}

.status-indicator.online {
  background: #4caf50;
}

.conversation-details {
  flex: 1;
  min-width: 0;
}

.conversation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.conversation-name {
  font-weight: 600;
  color: #333;
  font-size: 15px;
}

.conversation-time {
  color: #9e9e9e;
  font-size: 12px;
}

.conversation-last-message {
  color: #666;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unread-badge {
  background: #2196f3;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  margin-left: 8px;
}

/* Chat Area */
.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.chat-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.chat-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
}

.chat-user-info {
  display: flex;
  align-items: center;
}

.chat-avatar {
  position: relative;
  margin-right: 12px;
}

.chat-avatar img,
.chat-avatar .avatar-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.chat-user-details {
  display: flex;
  flex-direction: column;
}

.chat-user-name {
  font-weight: 600;
  color: #333;
  font-size: 16px;
}

.chat-user-status {
  color: #4caf50;
  font-size: 12px;
}

.chat-actions {
  display: flex;
  gap: 8px;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f8f9fa;
}

.message-group {
  margin-bottom: 20px;
  display: flex;
  align-items: flex-start;
}

.message-group.own-message {
  justify-content: flex-end;
}

.message-avatar {
  margin-right: 8px;
  margin-top: 4px;
}

.message-content {
  max-width: 70%;
}

.own-message .message-content {
  margin-left: auto;
}

.message-sender {
  color: #666;
  font-size: 12px;
  margin-bottom: 4px;
  font-weight: 500;
}

.message-bubble {
  background: white;
  padding: 12px 16px;
  border-radius: 18px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  margin-bottom: 4px;
}

.own-message .message-bubble {
  background: #2196f3;
  color: white;
}

.message-text {
  word-wrap: break-word;
  line-height: 1.4;
}

.message-time {
  color: #9e9e9e;
  font-size: 11px;
  text-align: right;
}

.own-message .message-time {
  text-align: left;
}

.message-input-area {
  padding: 16px 20px;
  border-top: 1px solid #e0e0e0;
  background: white;
}

.message-input-container {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  background: #f5f5f5;
  border-radius: 24px;
  padding: 8px;
}

.attachment-btn,
.voice-btn,
.emoji-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #666;
  transition: background-color 0.2s;
}

.attachment-btn:hover,
.voice-btn:hover,
.emoji-btn:hover {
  background: #e0e0e0;
}

.input-wrapper {
  flex: 1;
  margin: 0 8px;
}

.message-input {
  width: 100%;
  border: none;
  background: transparent;
  resize: none;
  outline: none;
  font-family: inherit;
  font-size: 14px;
  padding: 8px 0;
  min-height: 20px;
  max-height: 100px;
}

.send-btn {
  width: 36px;
  height: 36px;
  background: #2196f3;
  color: white;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s;
}

.send-btn:hover:not(:disabled) {
  background: #1976d2;
}

.send-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.no-conversation-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #666;
}

.welcome-content {
  text-align: center;
}

.welcome-content i {
  font-size: 48px;
  color: #ddd;
  margin-bottom: 16px;
}

.welcome-content h3 {
  margin: 0 0 8px 0;
  color: #333;
}

.welcome-content p {
  margin: 0;
  color: #666;
}

/* Right Sidebar */
.recently-added-sidebar {
  width: 280px;
  background: #424242;
  color: white;
  padding: 20px;
  overflow-y: auto;
}

.sidebar-section {
  margin-bottom: 32px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  font-weight: 600;
  font-size: 14px;
  color: #e0e0e0;
}

.user-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.user-item:hover {
  background: rgba(255,255,255,0.1);
}

.user-avatar {
  flex-shrink: 0;
}

.user-name {
  color: white;
  font-size: 14px;
  font-weight: 500;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

/* Continuing from modal-header */
.modal-header {
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #666;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background: #f5f5f5;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
  font-size: 14px;
}

.form-select,
.form-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.form-select:focus,
.form-input:focus {
  border-color: #2196f3;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #2196f3;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1976d2;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .recently-added-sidebar {
    display: none;
  }
}

@media (max-width: 768px) {
  .chat-view {
    flex-direction: column;
  }
  
  .conversations-sidebar {
    width: 100%;
    height: 50%;
  }
  
  .chat-area {
    height: 50%;
  }
  
  .conversation-item {
    padding: 16px;
  }
  
  .message-group {
    margin-bottom: 16px;
  }
  
  .message-content {
    max-width: 85%;
  }
  
  .modal {
    width: 95%;
    margin: 20px;
  }
}

@media (max-width: 480px) {
  .conversations-sidebar {
    width: 100%;
  }
  
  .chat-header {
    padding: 12px 16px;
  }
  
  .messages-area {
    padding: 16px;
  }
  
  .message-input-area {
    padding: 12px 16px;
  }
  
  .chats-title h2 {
    font-size: 20px;
  }
  
  .conversation-name {
    font-size: 14px;
  }
  
  .chat-user-name {
    font-size: 15px;
  }
}

/* Scrollbar Styling */
.conversations-list::-webkit-scrollbar,
.messages-area::-webkit-scrollbar,
.recently-added-sidebar::-webkit-scrollbar {
  width: 6px;
}

.conversations-list::-webkit-scrollbar-track,
.messages-area::-webkit-scrollbar-track,
.recently-added-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.conversations-list::-webkit-scrollbar-thumb,
.messages-area::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 3px;
}

.recently-added-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.conversations-list::-webkit-scrollbar-thumb:hover,
.messages-area::-webkit-scrollbar-thumb:hover {
  background: #bbb;
}

.recently-added-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Animation for new messages */
@keyframes messageSlideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-group:last-child .message-bubble {
  animation: messageSlideIn 0.3s ease-out;
}

/* Loading states */
.loading {
  opacity: 0.6;
  pointer-events: none;
}

/* Focus styles for accessibility */
.conversation-item:focus,
.icon-btn:focus,
.send-btn:focus,
.attachment-btn:focus,
.voice-btn:focus,
.emoji-btn:focus {
  outline: 2px solid #2196f3;
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .conversation-item {
    border: 1px solid transparent;
  }
  
  .conversation-item:hover {
    border-color: #333;
  }
  
  .conversation-item.active {
    border-color: #2196f3;
    background: #e3f2fd;
  }
  
  .message-bubble {
    border: 1px solid #ddd;
  }
  
  .own-message .message-bubble {
    border-color: #1976d2;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .conversation-item,
  .icon-btn,
  .message-bubble,
  .send-btn,
  .attachment-btn,
  .voice-btn,
  .emoji-btn,
  .user-item {
    transition: none;
  }
  
  .message-group:last-child .message-bubble {
    animation: none;
  }
}

/* Print styles */
@media print {
  .conversations-sidebar,
  .recently-added-sidebar,
  .chat-header,
  .message-input-area {
    display: none;
  }
  
  .chat-area {
    width: 100%;
  }
  
  .messages-area {
    height: auto;
    overflow: visible;
    background: white;
  }
  
  .message-bubble {
    box-shadow: none;
    border: 1px solid #ddd;
  }
}
</style>