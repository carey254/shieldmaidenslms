<template>
  <div class="captcha-container">
    <div class="captcha-display">
      <canvas ref="captchaCanvas" :width="150" :height="50"></canvas>
    </div>
    <div class="captcha-input">
      <input 
        type="text" 
        v-model="userInput" 
        placeholder="Enter the code above"
        class="captcha-input-field"
        @input="validateInput"
      />
      <button @click="generateCaptcha" class="captcha-refresh-btn" title="Refresh">
        🔄
      </button>
    </div>
    <div v-if="error" class="captcha-error">{{ error }}</div>
  </div>
</template>

<script>
export default {
  name: 'Captcha',
  data() {
    return {
      captchaText: '',
      userInput: '',
      error: ''
    }
  },
  mounted() {
    this.generateCaptcha()
  },
  methods: {
    generateCaptcha() {
      const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'
      this.captchaText = ''
      for (let i = 0; i < 6; i++) {
        this.captchaText += chars.charAt(Math.floor(Math.random() * chars.length))
      }
      this.drawCaptcha()
      this.userInput = ''
      this.error = ''
      this.$emit('captcha-changed', this.captchaText)
    },
    
    drawCaptcha() {
      const canvas = this.$refs.captchaCanvas
      const ctx = canvas.getContext('2d')
      
      // Clear canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height)
      
      // Background
      ctx.fillStyle = '#f0f0f0'
      ctx.fillRect(0, 0, canvas.width, canvas.height)
      
      // Draw noise lines
      for (let i = 0; i < 5; i++) {
        ctx.strokeStyle = `rgb(${Math.random() * 100}, ${Math.random() * 100}, ${Math.random() * 100})`
        ctx.beginPath()
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height)
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height)
        ctx.stroke()
      }
      
      // Draw text
      ctx.font = 'bold 24px Arial'
      ctx.fillStyle = '#333'
      ctx.textAlign = 'center'
      ctx.textBaseline = 'middle'
      
      for (let i = 0; i < this.captchaText.length; i++) {
        const x = 20 + i * 20
        const y = 25 + Math.random() * 10 - 5
        const angle = (Math.random() - 0.5) * 0.4
        
        ctx.save()
        ctx.translate(x, y)
        ctx.rotate(angle)
        ctx.fillText(this.captchaText[i], 0, 0)
        ctx.restore()
      }
    },
    
    validateInput() {
      this.error = ''
      if (this.userInput === this.captchaText) {
        this.$emit('captcha-verified', true)
      } else {
        this.$emit('captcha-verified', false)
      }
    },
    
    isValid() {
      return this.userInput === this.captchaText
    },
    
    getToken() {
      // Generate a token for backend validation
      return btoa(this.captchaText + ':' + Date.now())
    }
  }
}
</script>

<style scoped>
.captcha-container {
  margin: 10px 0;
}

.captcha-display {
  margin-bottom: 10px;
  display: inline-block;
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.captcha-input {
  display: flex;
  gap: 10px;
  align-items: center;
}

.captcha-input-field {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.captcha-input-field:focus {
  outline: none;
  border-color: #4CAF50;
}

.captcha-refresh-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 5px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.captcha-refresh-btn:hover {
  background-color: #f0f0f0;
}

.captcha-error {
  color: #f44336;
  font-size: 12px;
  margin-top: 5px;
}
</style>
