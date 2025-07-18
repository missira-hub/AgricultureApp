<template>
  <div class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <button class="close-btn" @click="close">×</button>
      <h2>Edit Profile</h2>

      <form @submit.prevent="updateProfile" enctype="multipart/form-data">
        <div class="avatar-wrapper">
          <img v-if="previewAvatar" :src="previewAvatar" class="avatar" />
          <div v-else class="avatar-placeholder">{{ userInitial }}</div>
          <label class="upload-btn">
            Change Avatar
            <input type="file" @change="handleFileChange" hidden />
          </label>
        </div>

        <label>
          Name:
          <input v-model="form.name" type="text" required />
        </label>

        <label>
          Email:
          <input v-model="form.email" type="email" required />
        </label>

        <label>
          Change Password:
          <input v-model="form.password" type="password" placeholder="Leave blank to keep current" />
        </label>

        <button type="submit">Update Profile</button>

        <div v-if="successMessage" class="success">{{ successMessage }}</div>
        <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineEmits, computed } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close', 'updated'])

const form = ref({
  name: '',
  email: '',
  password: '',
  avatar: null
})
const previewAvatar = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const isLoading = ref(true)

const userInitial = computed(() => {
  return form.value.name?.charAt(0)?.toUpperCase() || 'U'
})

const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('No token found')

    const { data } = await axios.get('http://127.0.0.1:8000/api/user', {
      headers: { Authorization: `Bearer ${token}` }
    })

    form.value.name = data.name
    form.value.email = data.email
    previewAvatar.value = data.avatar_url || null
  } catch (err) {
    errorMessage.value = 'Failed to fetch profile'
  } finally {
    isLoading.value = false
  }
}

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.value.avatar = file
    previewAvatar.value = URL.createObjectURL(file)
  }
}
const updateProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('No token found')

    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('email', form.value.email)

    if (form.value.password) {
      formData.append('password', form.value.password)
      formData.append('password_confirmation', form.value.password) // if required
    }

    if (form.value.avatar) {
      formData.append('avatar', form.value.avatar)
    }

    await axios.post('http://127.0.0.1:8000/api/profile/update', formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    successMessage.value = 'Profile updated successfully!'
    errorMessage.value = ''
    form.value.password = ''

    // Emit event to notify parent that profile was updated
    emit('updated')
  } catch (err) {
    errorMessage.value = 'Failed to update profile'
    successMessage.value = ''
  }
}

const close = () => emit('close')

onMounted(fetchProfile)
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 2rem;
  border-radius: 16px;
  width: 100%;
  max-width: 420px;
  position: relative;
  box-shadow: 0 0 20px rgba(0,0,0,0.2);
  font-family: 'Segoe UI', sans-serif;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 26px;
  cursor: pointer;
}

form label {
  display: block;
  margin: 1rem 0 0.5rem;
  font-weight: 600;
}

input[type="text"],
input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 14px;
}

button[type="submit"] {
  background-color: #22c55e;
  color: white;
  border: none;
  padding: 0.8rem 1.4rem;
  border-radius: 8px;
  margin-top: 1rem;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
}

button:hover {
  background-color: #16a34a;
}

.avatar-wrapper {
  text-align: center;
  margin-bottom: 1rem;
}

.avatar {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 0.5rem;
  border: 3px solid #22c55e;
}

.avatar-placeholder {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  background-color: #ccc;
  color: #444;
  font-size: 36px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.5rem;
}

.upload-btn {
  display: inline-block;
  background: #f3f4f6;
  border: 1px solid #ccc;
  padding: 6px 12px;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
}

.success {
  margin-top: 1rem;
  color: green;
}

.error {
  margin-top: 1rem;
  color: red;
}
</style>
