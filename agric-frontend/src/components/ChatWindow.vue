<template>
  <div class="messaging-container">
    <!-- Conversations List -->
    <aside class="conversations-list">
      <h2>Conversations</h2>
      <input
        v-model="search"
        placeholder="Search conversations..."
        @input="filterConversations"
        class="search-input"
      />
      <ul>
        <li
          v-for="conv in filteredConversations"
          :key="conv.id"
          :class="{ active: conv.id === selectedConversation?.id }"
          @click="selectConversation(conv)"
        >
          <div class="conv-name">{{ conv.participant_name }}</div>
          <div class="conv-last-message">{{ conv.last_message_preview }}</div>
          <div v-if="conv.unread_count > 0" class="unread-badge">
            {{ conv.unread_count }}
          </div>
        </li>
      </ul>
      <button @click="startNewConversation" class="new-conv-btn">
        + New Conversation
      </button>
    </aside>

    <!-- Messages Panel -->
    <section class="messages-panel" v-if="selectedConversation">
      <header>
        <h3>Chat with {{ selectedConversation.participant_name }}</h3>
      </header>
      <main class="messages-list" ref="messagesList">
        <div
          v-for="msg in messages"
          :key="msg.id"
          :class="['message', msg.sender_id === userId ? 'sent' : 'received']"
        >
          <p>{{ msg.body }}</p>
          <small>{{ formatDate(msg.created_at) }}</small>
        </div>
      </main>
      <form @submit.prevent="sendMessage" class="message-input-form">
        <input
          v-model="newMessage"
          placeholder="Type your message..."
          required
          autocomplete="off"
        />
        <button type="submit" :disabled="sending">Send</button>
      </form>
    </section>

    <section v-else class="no-conversation-selected">
      <p>Select a conversation to start chatting.</p>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, nextTick } from 'vue'
import axios from 'axios'

const userId = ref(null) // Set this from your auth system or API

const conversations = ref([])
const filteredConversations = ref([])
const search = ref('')
const selectedConversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const sending = ref(false)

const messagesList = ref(null)

// Fetch logged-in user ID (example)
async function fetchUserId() {
  try {
    const res = await axios.get('/api/user') // Adjust as needed
    userId.value = res.data.id
  } catch (error) {
    console.error('Error fetching user info', error)
  }
}

async function fetchConversations() {
  try {
    const res = await axios.get('/api/conversations')
    conversations.value = res.data
    filteredConversations.value = conversations.value
  } catch (error) {
    console.error('Error fetching conversations', error)
  }
}

function filterConversations() {
  const s = search.value.toLowerCase()
  filteredConversations.value = conversations.value.filter((conv) =>
    conv.participant_name.toLowerCase().includes(s) ||
    conv.last_message_preview.toLowerCase().includes(s)
  )
}

async function selectConversation(conv) {
  selectedConversation.value = conv
  await fetchMessages(conv.id)
  await markConversationRead(conv.id)
  scrollToBottom()
}

async function fetchMessages(conversationId) {
  try {
    const res = await axios.get(`/api/conversations/${conversationId}/messages`)
    messages.value = res.data
  } catch (error) {
    console.error('Error fetching messages', error)
  }
}

async function sendMessage() {
  if (!newMessage.value.trim()) return
  sending.value = true
  try {
    await axios.post(
      `/api/conversations/${selectedConversation.value.id}/send`,
      { body: newMessage.value }
    )
    newMessage.value = ''
    await fetchMessages(selectedConversation.value.id)
    await fetchConversations() // Update unread counts
    scrollToBottom()
  } catch (error) {
    console.error('Error sending message', error)
  } finally {
    sending.value = false
  }
}

async function markConversationRead(conversationId) {
  try {
    await axios.post(`/api/conversations/${conversationId}/read`)
    await fetchConversations()
  } catch (error) {
    console.error('Error marking conversation as read', error)
  }
}

async function startNewConversation() {
  // Simple prompt for demonstration; you can build a proper UI for choosing a user
  const userToChat = prompt('Enter the user ID to start conversation with:')
  if (!userToChat) return
  try {
    const res = await axios.post('/api/conversations/start', {
      participant_id: userToChat,
    })
    await fetchConversations()
    selectConversation(res.data) // Assuming API returns the created conversation object
  } catch (error) {
    console.error('Error starting conversation', error)
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesList.value) {
      messagesList.value.scrollTop = messagesList.value.scrollHeight
    }
  })
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleString()
}

// Initial fetches
onMounted(async () => {
  await fetchUserId()
  await fetchConversations()
})
</script>

<style scoped>
.messaging-container {
  display: flex;
  height: 90vh;
  font-family: 'Poppins', sans-serif;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

.conversations-list {
  width: 280px;
  border-right: 1px solid #ccc;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.conversations-list h2 {
  margin-bottom: 0.5rem;
}

.search-input {
  padding: 0.5rem;
  margin-bottom: 1rem;
  border-radius: 4px;
  border: 1px solid #ccc;
}

.conversations-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
  flex-grow: 1;
  overflow-y: auto;
}

.conversations-list li {
  padding: 0.6rem;
  border-radius: 5px;
  cursor: pointer;
  position: relative;
  margin-bottom: 0.3rem;
  background-color: #fafafa;
  transition: background-color 0.2s;
}

.conversations-list li.active,
.conversations-list li:hover {
  background-color: #007bff;
  color: white;
}

.conv-name {
  font-weight: 600;
}

.conv-last-message {
  font-size: 0.9rem;
  color: #555;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unread-badge {
  background: #dc3545;
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
  border-radius: 12px;
  padding: 0 6px;
  position: absolute;
  top: 8px;
  right: 8px;
}

.new-conv-btn {
  margin-top: 1rem;
  padding: 0.5rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.new-conv-btn:hover {
  background: #0056b3;
}

.messages-panel {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
}

.messages-panel header {
  border-bottom: 1px solid #ccc;
  padding-bottom: 0.5rem;
  margin-bottom: 0.5rem;
}

.messages-list {
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 0.5rem;
  margin-bottom: 0.5rem;
}

.message {
  max-width: 60%;
  padding: 0.6rem 1rem;
  border-radius: 20px;
  margin-bottom: 0.5rem;
  position: relative;
  word-wrap: break-word;
}

.sent {
  background-color: #007bff;
  color: white;
  margin-left: auto;
  border-bottom-right-radius: 4px;
}

.received {
  background-color: #f1f0f0;
  color: #333;
  margin-right: auto;
  border-bottom-left-radius: 4px;
}

.message small {
  display: block;
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 3px;
}

.received small {
  color: #555;
}

.message-input-form {
  display: flex;
  gap: 0.5rem;
}

.message-input-form input {
  flex-grow: 1;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  border: 1px solid #ccc;
  font-size: 1rem;
}

.message-input-form button {
  padding: 0 1rem;
  border: none;
  border-radius: 25px;
  background-color: #007bff;
  color: white;
  cursor: pointer;
  font-weight: 600;
}

.message-input-form button:disabled {
  background-color: #999;
  cursor: not-allowed;
}

.no-conversation-selected {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  font-size: 1.2rem;
  font-style: italic;
}
</style>
