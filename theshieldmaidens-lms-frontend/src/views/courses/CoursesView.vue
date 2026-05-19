<template>
  <div class="courses-page">
    <div class="courses-header">
      <h1>Available Courses</h1>
      <p>Explore our comprehensive range of courses designed to empower and educate</p>
    </div>

    <!-- Category Filter -->
    <div class="category-filters">
      <button 
        v-for="category in categories" 
        :key="category.id"
        @click="selectedCategory = selectedCategory === category.id ? '' : category.id"
        :class="['category-btn', { active: selectedCategory === category.id }]"
      >
        <i :class="category.icon"></i>
        {{ category.name }}
      </button>
    </div>

    <!-- Search Bar -->
    <div class="search-section">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search courses..."
          class="search-input"
        >
        <button class="search-btn">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>

    <!-- Courses Grid -->
    <div class="courses-grid">
      <div 
        v-for="course in filteredCourses" 
        :key="course.id" 
        class="course-card"
        @click="enrollCourse(course)"
      >
        <div class="course-image">
          <img :src="course.image || '/placeholder-course.jpg'" :alt="course.title">
          <div class="course-price">
            {{ course.price === 0 ? 'FREE' : `$${course.price}` }}
          </div>
        </div>
        <div class="course-content">
          <div class="course-category">{{ getCategoryName(course.category) }}</div>
          <h3>{{ course.title }}</h3>
          <p class="course-description">{{ course.description }}</p>
          
          <div class="course-meta">
            <div class="meta-item">
              <i class="fas fa-star"></i>
              <span>{{ course.rating || '4.5' }}</span>
            </div>
          </div>

          <div class="course-instructor">
            <img :src="course.instructorImage || '/instructor.jpg'" :alt="course.instructor">
            <div>
              <div class="instructor-name">{{ course.instructor || 'Shield Maidens Team' }}</div>
              <div class="instructor-title">Certified Instructor</div>
            </div>
          </div>

          <div class="course-actions">
            <button @click.stop="enrollCourse(course)" class="btn btn-primary btn-large">
              Enroll Now
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedCourse = ref(null)

// Initialize search query from URL parameter
onMounted(() => {
  if (route.query.search) {
    searchQuery.value = route.query.search
  }
})

// Course categories matching the curriculum
const categories = ref([
  { id: 'online-safety', name: 'Online Safety', icon: 'fas fa-shield-alt' },
  { id: 'online-harms', name: 'Online Harms', icon: 'fas fa-hand-holding-heart' },
  { id: 'digital-privacy', name: 'Digital Privacy', icon: 'fas fa-lock' },
  { id: 'cybersecurity-intro', name: 'Cybersecurity', icon: 'fas fa-user-shield' },
  { id: 'cybersecurity-fundamentals', name: 'Cybersecurity', icon: 'fas fa-user-shield' },
  { id: 'cyber-threats', name: 'Cyber Threats', icon: 'fas fa-exclamation-triangle' },
  { id: 'coding-youth', name: 'Coding', icon: 'fas fa-code' },
  { id: 'it-literacy', name: 'IT Literacy', icon: 'fas fa-desktop' },
  { id: 'digital-inclusion', name: 'Digital Inclusion', icon: 'fas fa-universal-access' },
  { id: 'public-speaking', name: 'Public Speaking', icon: 'fas fa-microphone' },
  { id: 'digital-ethics', name: 'Digital Ethics', icon: 'fas fa-balance-scale' },
  { id: 'media-literacy', name: 'Media Literacy', icon: 'fas fa-newspaper' }
])

// Updated course data matching the exact curriculum with module structure
const courses = ref([
  {
    id: 1,
    title: 'Online Safety',
    description: 'Understanding Online Safety, Protecting Personal Information, Recognizing Online Threats, Safe Communication & Reporting',
    category: 'online-safety',
    duration: '4 Weeks',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 156,
    rating: 4.7,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor1.jpg',
    enrolled: false,
    image: '/course1.jpg',
    modules: [
      {
        title: 'Module 1: Understanding Online Safety',
        subtopics: [
          'Introduction to Online Safety',
          'Common Online Risks & Threats',
          'Digital Footprints',
          'Why Online Safety Matters'
        ]
      },
      {
        title: 'Module 2: Protecting Personal Information',
        subtopics: [
          'Personal Data & Privacy',
          'Safe Use of Social Media',
          'Managing Online Profiles',
          'Avoiding Oversharing'
        ]
      },
      {
        title: 'Module 3: Recognizing Online Threats',
        subtopics: [
          'Online Scams & Fraud',
          'Fake Profiles & Impersonation',
          'Unsafe Links & Downloads',
          'Warning Signs of Online Danger'
        ]
      },
      {
        title: 'Module 4: Safe Communication & Reporting',
        subtopics: [
          'Safe Online Communication',
          'Handling Suspicious Interactions',
          'Blocking & Reporting',
          'Where to Get Help'
        ]
      }
    ],
    features: [
      'Introduction to Online Safety',
      'Common Online Risks & Threats',
      'Digital Footprints Management',
      'Personal Data & Privacy Protection',
      'Safe Use of Social Media',
      'Online Scams & Fraud Recognition',
      'Safe Online Communication',
      'Blocking & Reporting Techniques'
    ]
  },
  {
    id: 2,
    title: 'Introduction to Online Harms',
    description: 'Understanding TFGBV, Impact & Prevention, Response & Support for technology-facilitated gender-based violence',
    category: 'online-harms',
    duration: '3 Weeks',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 89,
    rating: 4.8,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor2.jpg',
    enrolled: false,
    image: '/course2.jpg',
    modules: [
      {
        title: 'Module 1: Understanding TFGBV',
        subtopics: [
          'What is TFGBV',
          'Forms of TFGBV',
          'Real-Life Examples',
          'Who is Affected'
        ]
      },
      {
        title: 'Module 2: Impact & Prevention',
        subtopics: [
          'Effects on Victims',
          'Psychological & Social Impact',
          'Prevention Strategies',
          'Staying Safe Online'
        ]
      },
      {
        title: 'Module 3: Response & Support',
        subtopics: [
          'Safe Response Actions',
          'Reporting Channels (e.g., 1195)',
          'Evidence Collection Basics',
          'Supporting Survivors'
        ]
      }
    ],
    features: [
      'What is TFGBV',
      'Forms of TFGBV',
      'Real-Life Examples',
      'Effects on Victims',
      'Psychological & Social Impact',
      'Prevention Strategies',
      'Safe Response Actions',
      'Reporting Channels (1195)'
    ]
  },
  {
    id: 3,
    title: 'Digital Safety & Privacy Essentials',
    description: 'Safe Internet Use, Password & Account Security, Privacy Protection, Data Safety & Recovery fundamentals',
    category: 'digital-privacy',
    duration: '4 Weeks',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 234,
    rating: 4.6,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor3.jpg',
    enrolled: false,
    image: '/course3.jpg',
    modules: [
      {
        title: 'Module 1: Safe Internet Use',
        subtopics: [
          'Safe Browsing Practices',
          'Identifying Harmful Content',
          'Avoiding Suspicious Links',
          'Netiquette Basics'
        ]
      },
      {
        title: 'Module 2: Password & Account Security',
        subtopics: [
          'Strong Password Creation',
          'Multi-Factor Authentication',
          'Securing Online Accounts',
          'Avoiding Unauthorized Access'
        ]
      },
      {
        title: 'Module 3: Privacy Protection',
        subtopics: [
          'Privacy Settings on Platforms',
          'Protecting Personal Data',
          'App Permissions & Tracking',
          'Data Sharing Risks'
        ]
      },
      {
        title: 'Module 4: Data Safety & Recovery',
        subtopics: [
          'Preventing Data Loss',
          'Backup Basics',
          'Account Recovery Methods',
          'Responding to Breaches'
        ]
      }
    ],
    features: [
      'Safe Browsing Practices',
      'Identifying Harmful Content',
      'Strong Password Creation',
      'Multi-Factor Authentication',
      'Securing Online Accounts',
      'Privacy Settings on Platforms',
      'Protecting Personal Data',
      'Preventing Data Loss & Recovery'
    ]
  },
  {
    id: 4,
    title: 'Introduction to Cybersecurity',
    description: 'Cybersecurity Basics, Cyber Hygiene & Safe Practices, Security Tools & Protection fundamentals',
    category: 'cybersecurity-intro',
    duration: '3 Weeks',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 178,
    rating: 4.7,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor4.jpg',
    enrolled: false,
    image: '/course4.jpg',
    modules: [
      {
        title: 'Module 1: Cybersecurity Basics',
        subtopics: [
          'What is Cybersecurity',
          'Importance in Daily Life',
          'Common Threats Overview'
        ]
      },
      {
        title: 'Module 2: Cyber Hygiene & Safe Practices',
        subtopics: [
          'Safe Device Usage',
          'Software Updates',
          'Antivirus Basics',
          'Secure Internet Use'
        ]
      },
      {
        title: 'Module 3: Security Tools & Protection',
        subtopics: [
          'Firewalls Basics',
          'Antivirus Tools',
          'Safe Networks (Wi-Fi)'
        ]
      }
    ],
    features: [
      'What is Cybersecurity',
      'Importance in Daily Life',
      'Common Threats Overview',
      'Safe Device Usage',
      'Software Updates & Maintenance',
      'Antivirus Basics',
      'Firewalls Fundamentals',
      'Secure Network Practices'
    ]
  },
  {
    id: 5,
    title: 'Cybersecurity Fundamentals',
    description: 'Networking Basics, System Security, Encryption & Data Protection, Human Factors & Risk Management',
    category: 'cybersecurity-fundamentals',
    duration: '4 Weeks',
    level: 'Intermediate',
    price: 0,
    enrolledStudents: 145,
    rating: 4.8,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor5.jpg',
    enrolled: false,
    image: '/course5.jpg',
    modules: [
      {
        title: 'Module 1: Networking Basics',
        subtopics: [
          'How Networks Work',
          'IP & Internet Basics',
          'Data Transmission'
        ]
      },
      {
        title: 'Module 2: System Security',
        subtopics: [
          'Firewalls & Antivirus',
          'Securing Devices',
          'Access Control Basics'
        ]
      },
      {
        title: 'Module 3: Encryption & Data Protection',
        subtopics: [
          'Basics of Encryption',
          'Protecting Data',
          'Secure Communication'
        ]
      },
      {
        title: 'Module 4: Human Factors & Risk',
        subtopics: [
          'Social Engineering',
          'Phishing Techniques',
          'Risk Awareness',
          'Intro to Ethical Hacking'
        ]
      }
    ],
    features: [
      'How Networks Work',
      'IP & Internet Basics',
      'Data Transmission',
      'Firewalls & Antivirus',
      'Securing Devices',
      'Access Control Basics',
      'Basics of Encryption',
      'Social Engineering Awareness'
    ]
  },
  {
    id: 6,
    title: 'Cyber Threats & Defense',
    description: 'Understanding Threats, Attack Techniques, Detection & Response strategies for cyber protection',
    category: 'cyber-threats',
    duration: '3 Weeks',
    level: 'Intermediate',
    price: 0,
    enrolledStudents: 167,
    rating: 4.6,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor6.jpg',
    enrolled: false,
    image: '/course6.jpg',
    modules: [
      {
        title: 'Module 1: Understanding Threats',
        subtopics: [
          'Malware & Ransomware',
          'Types of Cyber Attacks',
          'How Attacks Happen'
        ]
      },
      {
        title: 'Module 2: Attack Techniques',
        subtopics: [
          'Phishing Attacks',
          'DDoS Basics',
          'Hacking Methods'
        ]
      },
      {
        title: 'Module 3: Detection & Response',
        subtopics: [
          'Threat Detection Basics',
          'Incident Response',
          'Personal Defense Strategies'
        ]
      }
    ],
    features: [
      'Malware & Ransomware',
      'Types of Cyber Attacks',
      'How Attacks Happen',
      'Phishing Attack Techniques',
      'DDoS Basics',
      'Hacking Methods',
      'Threat Detection Basics',
      'Incident Response'
    ]
  },
  {
    id: 7,
    title: 'Coding for Kids and Youth',
    description: 'Introduction to Coding, Programming Basics, Scratch Programming, Building Projects and Web Basics',
    category: 'coding-youth',
    duration: '5 Weeks',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 198,
    rating: 4.9,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor7.jpg',
    enrolled: false,
    image: '/course7.jpg',
    modules: [
      {
        title: 'Module 1: Introduction to Coding',
        subtopics: [
          'What is Coding',
          'Computational Thinking',
          'Logic & Problem Solving'
        ]
      },
      {
        title: 'Module 2: Programming Basics',
        subtopics: [
          'Variables & Instructions',
          'Sequences & Loops',
          'Basic Logic'
        ]
      },
      {
        title: 'Module 3: Scratch Programming',
        subtopics: [
          'Scratch Interface',
          'Creating Animations',
          'Simple Games'
        ]
      },
      {
        title: 'Module 4: Building Projects',
        subtopics: [
          'Designing Projects',
          'Debugging Basics',
          'Improving Code'
        ]
      },
      {
        title: 'Module 5: Introduction to Web Basics',
        subtopics: [
          'HTML Basics',
          'Simple Page Design',
          'Final Mini Project'
        ]
      }
    ],
    features: [
      'What is Coding',
      'Computational Thinking',
      'Logic & Problem Solving',
      'Variables & Instructions',
      'Sequences & Loops',
      'Scratch Interface & Animations',
      'Creating Simple Games',
      'HTML Basics & Web Design'
    ]
  },
  {
    id: 8,
    title: 'Basic IT Literacy',
    description: 'Computer Basics, Software & Systems, File & Internet Use, Communication Tools fundamentals',
    category: 'it-literacy',
    duration: '4 Weeks',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 212,
    rating: 4.5,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor8.jpg',
    enrolled: false,
    image: '/course8.jpg',
    modules: [
      {
        title: 'Module 1: Computer Basics',
        subtopics: [
          'Parts of a Computer',
          'How Computers Work',
          'Types of Devices'
        ]
      },
      {
        title: 'Module 2: Software & Systems',
        subtopics: [
          'Operating Systems',
          'Applications',
          'Installing Software'
        ]
      },
      {
        title: 'Module 3: File & Internet Use',
        subtopics: [
          'File Management',
          'Internet Basics',
          'Safe Browsing'
        ]
      },
      {
        title: 'Module 4: Communication Tools',
        subtopics: [
          'Email Use',
          'Online Tools',
          'Basic Troubleshooting'
        ]
      }
    ],
    features: [
      'Parts of a Computer',
      'How Computers Work',
      'Types of Devices',
      'Operating Systems',
      'Applications & Software',
      'File Management',
      'Internet Basics & Safety',
      'Email & Online Tools'
    ]
  },
  {
    id: 9,
    title: 'Digital Inclusion & Accessibility',
    description: 'Digital Inclusion Basics, Accessibility in Technology, Inclusive Digital Practices',
    category: 'digital-inclusion',
    duration: '3 Weeks',
    level: 'Intermediate',
    price: 0,
    enrolledStudents: 134,
    rating: 4.7,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor9.jpg',
    enrolled: false,
    image: '/course9.jpg',
    modules: [
      {
        title: 'Module 1: Digital Inclusion Basics',
        subtopics: [
          'What is Digital Inclusion',
          'Importance of Access',
          'Digital Divide'
        ]
      },
      {
        title: 'Module 2: Accessibility in Technology',
        subtopics: [
          'Barriers to Access',
          'Assistive Technologies',
          'Inclusive Tools'
        ]
      },
      {
        title: 'Module 3: Inclusive Digital Practices',
        subtopics: [
          'Designing for Inclusion',
          'Supporting Diverse Users',
          'Accessibility Best Practices'
        ]
      }
    ],
    features: [
      'What is Digital Inclusion',
      'Importance of Access',
      'Digital Divide',
      'Barriers to Access',
      'Assistive Technologies',
      'Inclusive Tools',
      'Designing for Inclusion',
      'Accessibility Best Practices'
    ]
  },
  {
    id: 10,
    title: 'Public Speaking & Confidence Building',
    description: 'Building Confidence, Managing Fear, Speech Delivery Basics, Body Language, Practice Presentation',
    category: 'public-speaking',
    duration: '1 Week',
    level: 'Beginner',
    price: 0,
    enrolledStudents: 189,
    rating: 4.8,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor10.jpg',
    enrolled: false,
    image: '/course10.jpg',
    modules: [
      {
        title: 'Module:',
        subtopics: [
          'Building Confidence',
          'Managing Fear',
          'Speech Delivery Basics',
          'Body Language',
          'Practice Presentation'
        ]
      }
    ],
    features: [
      'Building Self-Confidence',
      'Managing Speaking Fear',
      'Speech Delivery Techniques',
      'Body Language Mastery',
      'Voice Projection & Clarity',
      'Audience Engagement',
      'Presentation Structure',
      'Practice & Feedback'
    ]
  },
  {
    id: 11,
    title: 'Online Ethics & Digital Citizenship',
    description: 'Digital Responsibility, Communication & Behavior, Safety & Responsibility in digital spaces',
    category: 'digital-ethics',
    duration: '3 Weeks',
    level: 'Intermediate',
    price: 0,
    enrolledStudents: 176,
    rating: 4.6,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor11.jpg',
    enrolled: false,
    image: '/course11.jpg',
    modules: [
      {
        title: 'Module 1: Digital Responsibility',
        subtopics: [
          'Digital Rights & Responsibilities',
          'Online Behavior',
          'Ethics in Technology'
        ]
      },
      {
        title: 'Module 2: Communication & Behavior',
        subtopics: [
          'Respectful Communication',
          'Online Conflicts',
          'Positive Digital Presence'
        ]
      },
      {
        title: 'Module 3: Safety & Responsibility',
        subtopics: [
          'Cyberbullying Prevention',
          'Responsible Content Sharing',
          'Building a Safe Digital Community'
        ]
      }
    ],
    features: [
      'Digital Rights & Responsibilities',
      'Online Behavior Ethics',
      'Respectful Communication',
      'Online Conflict Resolution',
      'Cyberbullying Prevention',
      'Responsible Content Sharing',
      'Building Safe Digital Communities',
      'Ethical Technology Use'
    ]
  },
  {
    id: 12,
    title: 'Media & Information Literacy (MIL)',
    description: 'Foundations of MIL, Fact Checking Information, Misinformation & Media Influence, Digital Participation',
    category: 'media-literacy',
    duration: '5 Weeks',
    level: 'Advanced',
    price: 0,
    enrolledStudents: 203,
    rating: 4.9,
    instructor: 'Shield Maidens Team',
    instructorImage: '/instructor12.jpg',
    enrolled: false,
    image: '/course12.jpg',
    modules: [
      {
        title: 'Module 1: Foundations of MIL',
        subtopics: [
          'Introduction to Media & Information Literacy',
          'Types of Media (TV, social media, news, etc.)',
          'Understanding Information Sources',
          'Role of Media in Society'
        ]
      },
      {
        title: 'Module 2: Fact Checking Information',
        subtopics: [
          'How to Search for Information Effectively',
          'Information Cycle',
          'Evaluating Sources (credible vs fake)',
          'Identifying Bias'
        ]
      },
      {
        title: 'Module 3: Misinformation, Media Influence & Ethics',
        subtopics: [
          'Fake News & Disinformation',
          'Hate Speech & Harmful Content',
          'Media Influence on Opinions & Behavior',
          'Responsible Media Use'
        ]
      },
      {
        title: 'Module 4: Digital Participation & Content Creation',
        subtopics: [
          'Digital Citizenship',
          'Creating Responsible Content',
          'Privacy & Data Protection',
          'Introduction to AI & Social Media Impact'
        ]
      }
    ],
    features: [
      'Types of Media & Information Sources',
      'Effective Information Searching',
      'Evaluating Source Credibility',
      'Identifying Bias & Manipulation',
      'Fake News & Disinformation',
      'Media Influence Analysis',
      'Responsible Media Use',
      'Creating Responsible Content'
    ]
  }
])

const defaultFeatures = [
  'Comprehensive learning materials',
  'Interactive exercises and activities',
  'Expert instruction and support',
  'Certificate of completion',
  'Lifetime access to course content'
]

const filteredCourses = computed(() => {
  return courses.value.filter(course => {
    const matchesSearch = course.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         course.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !selectedCategory.value || course.category === selectedCategory.value
    
    return matchesSearch && matchesCategory
  })
})

const getCategoryName = (category) => {
  const categories = {
    'online-safety': 'Online Safety',
    'online-harms': 'Online Harms',
    'digital-privacy': 'Digital Privacy',
    'cybersecurity-intro': 'Cybersecurity',
    'cybersecurity-fundamentals': 'Cybersecurity',
    'cyber-threats': 'Cyber Threats',
    'coding-youth': 'Coding',
    'it-literacy': 'IT Literacy',
    'digital-inclusion': 'Digital Inclusion',
    'public-speaking': 'Public Speaking',
    'digital-ethics': 'Digital Ethics',
    'media-literacy': 'Media Literacy'
  }
  return categories[category] || category
}


const closeCourseModal = () => {
  selectedCourse.value = null
}

const enrollCourse = (course) => {
  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    // Redirect to login with return URL
    authStore.setReturnUrl(`/courses/${course.id}`)
    router.push('/login')
    return
  }

  // Store course info for post-login enrollment
  localStorage.setItem('courseToEnroll', JSON.stringify({ id: course.id }))
  
  // Redirect to enrollment page
  router.push(`/course/${course.id}/enroll`)
}

</script>

<style scoped>
.courses-page {
  padding: 1rem;
  max-width: 1400px;
  margin: 0 auto;
}

.courses-header {
  text-align: center;
  margin-bottom: 3rem;
}

.courses-header h1 {
  color: #1a365d;
  font-size: 2rem;
  margin-bottom: 1rem;
}

.courses-header p {
  color: #64748b;
  font-size: 1.125rem;
  max-width: 600px;
  margin: 0 auto;
}

.category-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
  margin-bottom: 2rem;
}

.category-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
}

.category-btn:hover {
  border-color: #1a365d;
  background: #f8fafc;
}

.category-btn.active {
  background: #1a365d;
  color: white;
  border-color: #1a365d;
}

.search-section {
  max-width: 600px;
  margin: 0 auto 3rem;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 100%;
  padding: 1rem 3rem 1rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 50px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #1a365d;
}

.search-btn {
  position: absolute;
  right: 0.5rem;
  background: #1a365d;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.course-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}

.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.course-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.course-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.course-price {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #10b981;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-weight: 600;
  font-size: 0.875rem;
}

.course-content {
  padding: 1.5rem;
}

.course-category {
  color: #1a365d;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.course-content h3 {
  margin: 0 0 0.75rem 0;
  color: #1a365d;
  font-size: 1.25rem;
  line-height: 1.4;
}

.course-description {
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #64748b;
  font-size: 0.875rem;
}

.course-instructor {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 0;
  border-top: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 1rem;
}

.course-instructor img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.instructor-name {
  font-weight: 600;
  color: #1a365d;
  font-size: 0.875rem;
}

.instructor-title {
  color: #64748b;
  font-size: 0.75rem;
}

.course-actions {
  display: flex;
  gap: 0.75rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
  flex: 1;
}

.btn-primary {
  background: #1a365d;
  color: white;
}

.btn-primary:hover {
  background: #2c5282;
  transform: translateY(-1px);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.btn-outline {
  background: white;
  color: #1a365d;
  border: 2px solid #1a365d;
}

.btn-outline:hover {
  background: #1a365d;
  color: white;
}

.btn-large {
  padding: 1rem 2rem;
  font-size: 1rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
}

.course-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  padding: 1.5rem;
}

.detail-image img {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 12px;
}

.detail-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.category {
  background: #f1f5f9;
  color: #1a365d;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.price {
  background: #10b981;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.detail-content h3 {
  color: #1a365d;
  margin-bottom: 1rem;
}

.detail-overview {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.detail-overview h3 {
  color: #1a365d;
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.overview-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.overview-item i {
  font-size: 1.25rem;
  color: #ff9900;
  width: 24px;
}

.overview-item .label {
  display: block;
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.overview-item .value {
  font-weight: 600;
  color: #1a365d;
}

.detail-description {
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.detail-description h4 {
  color: #1a365d;
  margin-bottom: 0.75rem;
}

.detail-modules {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.detail-modules h4 {
  color: #1a365d;
  margin-bottom: 1rem;
  font-size: 1.125rem;
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.module-item {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
}

.module-number {
  background: #ff9900;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
}

.module-title {
  color: #1a365d;
  font-weight: 600;
  font-size: 1rem;
}

.module-content {
  color: #64748b;
  line-height: 1.6;
}

.module-content p {
  margin: 0;
}

.detail-info {
  margin: 1.5rem 0;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  color: #374151;
}

.detail-actions {
  margin-top: 2rem;
}

@media (max-width: 768px) {
  .courses-page {
    padding: 1rem;
  }
  
  .courses-header h1 {
    font-size: 2rem;
  }
  
  .category-filters {
    justify-content: flex-start;
    overflow-x: auto;
    flex-wrap: nowrap;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
  }
  
  .course-details {
    grid-template-columns: 1fr;
  }
  
  .modal-overlay {
    padding: 1rem;
  }
}
</style>
