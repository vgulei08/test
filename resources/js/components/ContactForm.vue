<script setup>
import { ref, computed } from 'vue'
import { useVuelidate } from '@vuelidate/core'
import { required, email, minLength, maxLength, helpers } from '@vuelidate/validators'

// Custom validators
const notGmail = helpers.withMessage(
  'Gmail addresses are not allowed',
  (value) => !value.toLowerCase().endsWith('@gmail.com')
)

const phonePrefix = helpers.withMessage(
  'Phone number must start with + and country code',
  (value) => /^\+\d{1,3}/.test(value)
)

const validZip = helpers.withMessage(
  'Invalid ZIP code format',
  (value) => /^\d{5}(-\d{4})?$/.test(value)
)

// Form state
const formData = ref({
  name: '',
  email: '',
  phone: '',
  message: '',
  street: '',
  state: '',
  zip: '',
  country: '',
  images: null,
  files: null
})

const loading = ref(false)
const error = ref(null)
const success = ref(false)

// Validation rules
const rules = computed(() => ({
  name: { 
    required, 
    minLength: minLength(2),
    maxLength: maxLength(10)
  },
  email: { 
    required, 
    email,
    notGmail 
  },
  phone: { 
    required,
    phonePrefix
  },
  message: { 
    required,
    minLength: minLength(10)
  },
  street: { required },
  state: { required },
  zip: { 
    required,
    validZip
  },
  country: { required },
  images: { required },
  files: { required }
}))

const v$ = useVuelidate(rules, formData)

// File handling
const handleImageUpload = (event) => {
  const input = event.target
  if (!input.files?.length) return
  
  const files = Array.from(input.files)
  const validFiles = files.filter(file => file.type.startsWith('image/jpeg'))
  
  if (validFiles.length !== files.length) {
    alert('Only JPG images are allowed')
    input.value = ''
    return
  }
  
  formData.value.images = validFiles
}

const handleFileUpload = (event) => {
  const input = event.target
  if (!input.files?.length) return
  
  const files = Array.from(input.files)
  const validFiles = files.filter(file => file.type === 'application/pdf')
  
  if (validFiles.length !== files.length) {
    alert('Only PDF files are allowed')
    input.value = ''
    return
  }
  
  formData.value.files = validFiles
}

// Form submission
const handleSubmit = async () => {
  try {
    const isFormValid = await v$.value.$validate()
    if (!isFormValid) {
      alert('Please fix the form errors')
      return
    }

    loading.value = true
    error.value = null
    success.value = false

    // Create FormData object for file uploads
    const formDataToSend = new FormData()
    
    // Add text fields
    Object.entries(formData.value).forEach(([key, value]) => {
      if (key !== 'images' && key !== 'files') {
        formDataToSend.append(key, value)
      }
    })
    
    // Add images
    if (formData.value.images) {
      formData.value.images.forEach((image, index) => {
        formDataToSend.append(`images[${index}]`, image)
      })
    }
    
    // Add files
    if (formData.value.files) {
      formData.value.files.forEach((file, index) => {
        formDataToSend.append(`files[${index}]`, file)
      })
    }

    // Replace with your Laravel API endpoint
    const response = await fetch('http://your-laravel-api.test/api/contacts', {
      method: 'POST',
      body: formDataToSend,
    })

    if (!response.ok) {
      throw new Error('Failed to submit form')
    }

    success.value = true
    // Reset form
    formData.value = {
      name: '',
      email: '',
      phone: '',
      message: '',
      street: '',
      state: '',
      zip: '',
      country: '',
      images: null,
      files: null
    }
    v$.value.$reset()
    
    // Reset file inputs
    const imageInput = document.getElementById('images')
    const fileInput = document.getElementById('files')
    if (imageInput) imageInput.value = ''
    if (fileInput) fileInput.value = ''

  } catch (e) {
    error.value = e.message || 'An error occurred'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container mt-5">
    <h2 class="mb-4">Contact Form</h2>

    <div v-if="success" class="alert alert-success" role="alert">
      Form submitted successfully!
    </div>

    <div v-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="needs-validation">
      <!-- Name -->
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input
          type="text"
          class="form-control"
          :class="{ 'is-invalid': v$.name.$error }"
          id="name"
          v-model="formData.name"
        >
        <div class="invalid-feedback" v-if="v$.name.$error">
          {{ v$.name.$errors[0].$message }}
        </div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input
          type="email"
          class="form-control"
          :class="{ 'is-invalid': v$.email.$error }"
          id="email"
          v-model="formData.email"
        >
        <div class="invalid-feedback" v-if="v$.email.$error">
          {{ v$.email.$errors[0].$message }}
        </div>
      </div>

      <!-- Phone -->
      <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input
          type="tel"
          class="form-control"
          :class="{ 'is-invalid': v$.phone.$error }"
          id="phone"
          v-model="formData.phone"
          placeholder="+1234567890"
        >
        <div class="invalid-feedback" v-if="v$.phone.$error">
          {{ v$.phone.$errors[0].$message }}
        </div>
      </div>

      <!-- Message -->
      <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea
          class="form-control"
          :class="{ 'is-invalid': v$.message.$error }"
          id="message"
          v-model="formData.message"
          rows="3"
        ></textarea>
        <div class="invalid-feedback" v-if="v$.message.$error">
          {{ v$.message.$errors[0].$message }}
        </div>
      </div>

      <!-- Address Fields -->
      <div class="row">
        <div class="col-12 mb-3">
          <label for="street" class="form-label">Street</label>
          <input
            type="text"
            class="form-control"
            :class="{ 'is-invalid': v$.street.$error }"
            id="street"
            v-model="formData.street"
          >
          <div class="invalid-feedback" v-if="v$.street.$error">
            Street is required
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <label for="state" class="form-label">State</label>
          <input
            type="text"
            class="form-control"
            :class="{ 'is-invalid': v$.state.$error }"
            id="state"
            v-model="formData.state"
          >
          <div class="invalid-feedback" v-if="v$.state.$error">
            State is required
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <label for="zip" class="form-label">ZIP</label>
          <input
            type="text"
            class="form-control"
            :class="{ 'is-invalid': v$.zip.$error }"
            id="zip"
            v-model="formData.zip"
          >
          <div class="invalid-feedback" v-if="v$.zip.$error">
            {{ v$.zip.$errors[0].$message }}
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <label for="country" class="form-label">Country</label>
          <input
            type="text"
            class="form-control"
            :class="{ 'is-invalid': v$.country.$error }"
            id="country"
            v-model="formData.country"
          >
          <div class="invalid-feedback" v-if="v$.country.$error">
            Country is required
          </div>
        </div>
      </div>

      <!-- File Uploads -->
      <div class="mb-3">
        <label for="images" class="form-label">Images (JPG only)</label>
        <input
          type="file"
          class="form-control"
          :class="{ 'is-invalid': v$.images.$error }"
          id="images"
          accept=".jpg,.jpeg"
          multiple
          @change="handleImageUpload"
        >
        <div class="invalid-feedback" v-if="v$.images.$error">
          Images are required
        </div>
      </div>

      <div class="mb-3">
        <label for="files" class="form-label">Files (PDF only)</label>
        <input
          type="file"
          class="form-control"
          :class="{ 'is-invalid': v$.files.$error }"
          id="files"
          accept=".pdf"
          multiple
          @change="handleFileUpload"
        >
        <div class="invalid-feedback" v-if="v$.files.$error">
          Files are required
        </div>
      </div>

      <button 
        type="submit" 
        class="btn btn-primary"
        :disabled="loading"
      >
        <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
        {{ loading ? 'Submitting...' : 'Submit' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.container {
  max-width: 800px;
}
</style>