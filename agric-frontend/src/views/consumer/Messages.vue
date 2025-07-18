<template>
  <div class="messaging-container">
    <!-- Header with recipient name -->
    <header class="message-header">
      <h1>Jane Smith</h1>
    </header>
    
    <!-- Messages container -->
    <div class="messages-container" ref="messagesContainer">
      <!-- Received message -->
      <div class="message received">
        <div class="message-content">Hi, good morning!</div>
      </div>
      
      <!-- Received message -->
      <div class="message received">
        <div class="message-content">hope you doing great things today.</div>
      </div>
      
      <!-- Received message -->
      <div class="message received">
        <div class="message-content">Hi, God bless you!</div>
      </div>
      
      <!-- Sent message -->
      <div class="message sent">
        <div class="message-content">Thank you so much!</div>
      </div>
      
      <!-- Received message -->
      <div class="message received">
        <div class="message-content">You are welcome.</div>
      </div>
      
      <!-- More messages from your screenshot -->
      <div class="message sent">
        <div class="message-content">Hi, everyone! My name is John, and nice to meet you all. Let's be friends.</div>
      </div>
      
      <div class="message sent">
        <div class="message-content">Get well very soon!</div>
      </div>
      
      <div class="message received">
        <div class="message-content">Hope you are doing well 😊</div>
      </div>
      
      <div class="message received">
        <div class="message-content">No 💬</div>
      </div>
      
      <div class="message sent">
        <div class="message-content">Love you.</div>
      </div>
      
      <div class="message received">
        <div class="message-content">Me too.</div>
      </div>
      
      <div class="message received">
        <div class="message-content">Hi, good morning!</div>
      </div>
      
      <div class="message received">
        <div class="message-content">hope you doing great things today.</div>
      </div>
      
      <div class="message sent">
        <div class="message-content">I love you to the moon and back 😊</div>
      </div>
      
      <div class="message sent">
        <div class="message-content">Text me later 💬</div>
      </div>
      
      <div class="message sent">
        <div class="message-content">From the bottom of my heart, thank you for your help.</div>
      </div>
      
      <div class="message received">
        <div class="message-content">Hi!</div>
      </div>
      
      <div class="message received">
        <div class="message-content">Good day.</div>
      </div>
      
      <div class="message sent">
        <div class="message-content">See you tomorrow!</div>
      </div>
      
      <div class="message received">
        <div class="message-content">Happy holidays 😊</div>
      </div>
    </div>
    
    <!-- Input area with keyboard -->
    <div class="input-container">
      <input 
        type="text" 
        placeholder="Type something..." 
        class="message-input"
        v-model="newMessage"
      />
      
      <!-- Keyboard rows -->
      <div class="keyboard-row">
        <button v-for="char in firstRow" :key="char" @click="addChar(char)">{{ char }}</button>
      </div>
      <div class="keyboard-row">
        <button v-for="char in secondRow" :key="char" @click="addChar(char)">{{ char }}</button>
      </div>
      <div class="keyboard-row">
        <button @click="addChar('♦')">♦</button>
        <button v-for="char in thirdRow" :key="char" @click="addChar(char)">{{ char }}</button>
        <button @click="addEmoji('💬')">💬</button>
      </div>
      <div class="keyboard-row">
        <button @click="addChar('123')">123</button>
        <button @click="addEmoji('👍')">👍</button>
        <button @click="addChar(' ')" class="space">space</button>
        <button @click="sendMessage" class="return">return</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      newMessage: '',
      firstRow: ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
      secondRow: ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
      thirdRow: ['Z', 'X', 'C', 'V', 'B', 'N', 'M'],
      quickMessages: [
        "Hi, good morning!",
        "Hope you are doing well 😊",
        "Thank you so much!",
        "You are welcome.",
        "Get well very soon!",
        "Love you.",
        "See you tomorrow!"
      ]
    }
  },
  methods: {
    addChar(char) {
      this.newMessage += char;
    },
    addEmoji(emoji) {
      this.newMessage += emoji;
    },
    sendMessage() {
      if (this.newMessage.trim()) {
        // Here you would normally send to your API
        console.log('Sending:', this.newMessage);
        this.newMessage = '';
      }
    },
    sendQuickMessage(msg) {
      this.newMessage = msg;
      this.sendMessage();
    }
  },
  mounted() {
    // Scroll to bottom on load
    this.$nextTick(() => {
      this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
    });
  }
}
</script>

<style scoped>
.messaging-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: #f5f5f5;
  font-family: -apple-system, BlinkMacSystemFont, sans-serif;
}

.message-header {
  padding: 15px;
  background-color: #6200ee;
  color: white;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.messages-container {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.message {
  max-width: 70%;
  padding: 10px 15px;
  border-radius: 18px;
  word-wrap: break-word;
}

.message.received {
  background-color: #e0e0e0;
  align-self: flex-start;
  border-bottom-left-radius: 4px;
}

.message.sent {
  background-color: #6200ee;
  color: white;
  align-self: flex-end;
  border-bottom-right-radius: 4px;
}

.input-container {
  padding: 10px;
  background-color: white;
  border-top: 1px solid #ddd;
}

.message-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 24px;
  margin-bottom: 10px;
  font-size: 16px;
}

.keyboard-row {
  display: flex;
  justify-content: center;
  margin-bottom: 5px;
}

.keyboard-row button {
  padding: 10px;
  margin: 2px;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: white;
  cursor: pointer;
  font-size: 16px;
  min-width: 30px;
}

.keyboard-row button.space {
  flex-grow: 1;
}

.keyboard-row button.return {
  background-color: #6200ee;
  color: white;
}
</style>