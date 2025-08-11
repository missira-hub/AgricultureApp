<template>
  <section class="messaging-section">
    <div class="messaging-container">
      <!-- Conversations List -->
      <div class="conversation-list">
        <h3>Conversations</h3>
        <div v-if="loadingConversations" class="loading">Loading conversations...</div>
        <div v-else-if="conversations.length === 0" class="empty-state">
          No conversations yet
        </div>
        <ul v-else>
          <li
            v-for="conv in conversations"
            :key="conv.id"
            @click="selectConversation(conv)"
            :class="{ active: currentConversation?.id === conv.id }"
          >
            <div class="conversation-info">
              <strong>{{ getConversationTitle(conv) }}</strong>
              <p class="last-message">{{ getLastMessagePreview(conv) }}</p>
            </div>
          </li>
        </ul>
      </div>

      <!-- Chat Area -->
      <div class="chat-area">
        <div v-if="!currentConversation" class="select-conversation">
          <p>Select a conversation to start chatting</p>
        </div>
        <div v-else>
          <div class="chat-header">
            <h4>{{ getConversationTitle(currentConversation) }}</h4>
          </div>
          <div class="messages-container" ref="messagesContainer">
            <div v-if="loadingMessages" class="loading">Loading messages...</div>
            <div v-else-if="!currentConversation.messages || currentConversation.messages.length === 0" class="empty-state">
              No messages yet
            </div>
            <div v-else>
              <div
                v-for="message in currentConversation.messages"
                :key="message.id"
                class="message"
                :class="{ 'my-message': message.sender_id === user.id }"
              >
                <div class="message-content">
                  <p>{{ message.message }}</p>
                  <small>{{ formatMessageTime(message.created_at) }}</small>
                </div>
              </div>
            </div>
          </div>
          <div class="message-input">
            <input
              v-model="newMessage"
              @keyup.enter="sendMessage"
              placeholder="Type your message..."
            />
            <button @click="sendMessage" :disabled="!newMessage.trim()">
              Send
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import axios from 'axios'

const loadingConversations = ref(false)
const loadingMessages = ref(false)
const conversations = ref([])
const currentConversation = ref(null)
const newMessage = ref('')
const user = { id: 1 } // Replace with actual logged in user ID or fetched user

// Load conversations on mounted or as needed
const loadConversations = async () => {
  loadingConversations.value = true
  try {
    const res = await axios.get('/api/conversations', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    conversations.value = res.data
  } catch (err) {
    console.error('Error loading conversations:', err)
  } finally {
    loadingConversations.value = false
  }
}

// Select conversation and load messages
const selectConversation = async (conversation) => {
  loadingMessages.value = true
  try {
    const res = await axios.get(`/api/conversations/${conversation.id}/messages`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    currentConversation.value = { ...conversation, messages: res.data }
  } catch (err) {
    console.error('Error loading messages:', err)
  } finally {
    loadingMessages.value = false
    nextTick(() => {
      const container = document.querySelector('.messages-container')
      if (container) {
        container.scrollTop = container.scrollHeight
      }
    })
  }
}

// Send a message
const sendMessage = async () => {
  if (!newMessage.value.trim() || !currentConversation.value) return
  try {
    const res = await axios.post(
      `/api/conversations/${currentConversation.value.id}/messages`,
      { message: newMessage.value.trim() },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
    )
    currentConversation.value.messages.push(res.data)
    newMessage.value = ''
    nextTick(() => {
      const container = document.querySelector('.messages-container')
      if (container) {
        container.scrollTop = container.scrollHeight
      }
    })
  } catch (err) {
    console.error('Failed to send message:', err)
  }
}

// Helpers to display conversation titles and message previews
const getConversationTitle = (conv) => {
  return conv.title || `Conversation #${conv.id}`
}
const getLastMessagePreview = (conv) => {
  if (!conv.messages || conv.messages.length === 0) return 'No messages yet'
  return conv.messages[conv.messages.length - 1].message.slice(0, 40) + '...'
}

// On component mount, load conversations
loadConversations()
</script>

<style scoped>
.messaging-section {
  padding: 1rem;
  height: 100%;
}

.messaging-container {
  display: flex;
  gap: 1rem;
  height: 80vh;
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
  background: white;
}

/* Conversations list styles */
.conversation-list {
  width: 320px;
  border-right: 1px solid #ddd;
  padding: 1rem;
  overflow-y: auto;
  background: #fafafa;
}

.conversation-list h3 {
  margin-bottom: 1rem;
}

.conversation-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.conversation-list li {
  padding: 0.75rem;
  cursor: pointer;
  border-radius: 6px;
  margin-bottom: 0.5rem;
  transition: background-color 0.2s ease;
}

.conversation-list li:hover,
.conversation-list li.active {
  background-color: #3b82f6;
  color: white;
}

/* Chat area styles */
.chat-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
}

.chat-header {
  border-bottom: 1px solid #ddd;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
}

.messages-container {
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 0.5rem;
}

.message {
  margin-bottom: 1rem;
  max-width: 70%;
  padding: 0.6rem 1rem;
  border-radius: 12px;
  background: #f3f4f6;
  color: #111;
}

.my-message {
  background: #3b82f6;
  color: white;
  margin-left: auto;
}

.message-content p {
  margin: 0;
}

.message-content small {
  font-size: 0.75rem;
  opacity: 0.6;
  display: block;
  margin-top: 0.25rem;
}

.message-input {
  margin-top: 1rem;
  display: flex;
  gap: 0.5rem;
}

.message-input input {
  flex-grow: 1;
  padding: 0.5rem;
  font-size: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.message-input button {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.message-input button:disabled {
  background-color: #93c5fd;
  cursor: not-allowed;
}

.message-input button:hover:not(:disabled) {
  background-color: #2563eb;
}

.empty-state {
  color: #6b7280;
  text-align: center;
  margin-top: 2rem;
}

.loading {
  text-align: center;
  margin-top: 2rem;
  color: #2563eb;
}
</style>
