// nuxt.config.js
export default defineNuxtConfig({
  // Включаем SSR
  ssr: true,
  
  // Настройки приложения
  app: {
    head: {
      title: 'FckerMVC App',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ]
    }
  },
  
  // Настройки runtime config
  runtimeConfig: {
    // Приватные ключи (только на сервере)
    jwtSecret: process.env.JWT_SECRET,
    
    // Публичные ключи (доступны на клиенте)
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost:8000'
    }
  },
  
  // Модули
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],
  
  // Настройки CSS
  css: [
    '~/assets/css/main.css'
  ],
  
  // Настройки Vite
  vite: {
    define: {
      'process.env': {}
    }
  },
  
  // Настройки Nitro
  nitro: {
    devProxy: {
      '/api': {
        target: process.env.API_BASE_URL || 'http://localhost:8000',
        changeOrigin: true,
        prependPath: false
      }
    }
  }
})

// plugins/api.js
export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  
  const api = {
    async request(endpoint, options = {}) {
      const token = useCookie('access_token')
      const refreshToken = useCookie('refresh_token')
      
      try {
        const response = await $fetch(`${config.public.apiBase}${endpoint}`, {
          ...options,
          headers: {
            'Content-Type': 'application/json',
            ...(token.value && { Authorization: `Bearer ${token.value}` }),
            ...options.headers
          }
        })
        
        return response
      } catch (error) {
        // Если токен истек, пытаемся обновить
        if (error.status === 401 && refreshToken.value) {
          try {
            const refreshResponse = await $fetch(`${config.public.apiBase}/auth/refresh`, {
              method: 'POST',
              body: { refresh_token: refreshToken.value }
            })
            
            // Обновляем токены
            token.value = refreshResponse.data.tokens.access_token
            refreshToken.value = refreshResponse.data.tokens.refresh_token
            
            // Повторяем запрос с новым токеном
            return await $fetch(`${config.public.apiBase}${endpoint}`, {
              ...options,
              headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token.value}`,
                ...options.headers
              }
            })
          } catch (refreshError) {
            // Если не удалось обновить токен, очищаем куки и перенаправляем на логин
            token.value = null
            refreshToken.value = null
            await navigateTo('/login')
            throw refreshError
          }
        }
        
        throw error
      }
    },
    
    // Методы для работы с постами
    posts: {
      async getAll(params = {}) {
        return await api.request('/posts', { params })
      },
      
      async getById(id) {
        return await api.request(`/posts/${id}`)
      },
      
      async create(data) {
        return await api.request('/posts', {
          method: 'POST',
          body: data
        })
      },
      
      async update(id, data) {
        return await api.request(`/posts/${id}`, {
          method: 'PUT',
          body: data
        })
      },
      
      async delete(id) {
        return await api.request(`/posts/${id}`, {
          method: 'DELETE'
        })
      },
      
      async getMy(params = {}) {
        return await api.request('/posts/my', { params })
      }
    },
    
    // Методы для аутентификации
    auth: {
      async register(data) {
        return await api.request('/auth/register', {
          method: 'POST',
          body: data
        })
      },
      
      async login(data) {
        return await api.request('/auth/login', {
          method: 'POST',
          body: data
        })
      },
      
      async me() {
        return await api.request('/auth/me')
      },
      
      async logout() {
        return await api.request('/auth/logout', {
          method: 'POST'
        })
      }
    }
  }
  
  return {
    provide: {
      api
    }
  }
})

// composables/useAuth.js
export const useAuth = () => {
  const { $api } = useNuxtApp()
  const user = useState('user', () => null)
  const isAuthenticated = computed(() => !!user.value)
  
  const login = async (credentials) => {
    try {
      const response = await $api.auth.login(credentials)
      user.value = response.data.user
      return response
    } catch (error) {
      throw error
    }
  }
  
  const register = async (userData) => {
    try {
      const response = await $api.auth.register(userData)
      user.value = response.data.user
      return response
    } catch (error) {
      throw error
    }
  }
  
  const logout = async () => {
    try {
      await $api.auth.logout()
      user.value = null
    } catch (error) {
      console.error('Logout error:', error)
    }
  }
  
  const checkAuth = async () => {
    try {
      const response = await $api.auth.me()
      user.value = response.data.user
      return true
    } catch (error) {
      user.value = null
      return false
    }
  }
  
  return {
    user: readonly(user),
    isAuthenticated,
    login,
    register,
    logout,
    checkAuth
  }
}

// middleware/auth.js
export default defineNuxtRouteMiddleware((to, from) => {
  const { isAuthenticated } = useAuth()
  
  if (!isAuthenticated.value) {
    return navigateTo('/login')
  }
})

// middleware/guest.js
export default defineNuxtRouteMiddleware((to, from) => {
  const { isAuthenticated } = useAuth()
  
  if (isAuthenticated.value) {
    return navigateTo('/dashboard')
  }
}) 