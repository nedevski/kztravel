import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useRef,
  useState,
  type ReactNode,
} from 'react'

export type Theme = 'light' | 'dark'

const STORAGE_KEY = 'kz-theme'
const THEME_TRANSITION_MS = 350
const THEME_TRANSITION_CLASS = 'theme-transition'

const ThemeContext = createContext<{
  theme: Theme
  toggleTheme: () => void
} | null>(null)

function getInitialTheme(): Theme {
  if (typeof window === 'undefined') return 'light'
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored === 'light' || stored === 'dark') return stored
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

export function ThemeProvider({ children }: { children: ReactNode }) {
  const [theme, setTheme] = useState<Theme>(getInitialTheme)
  const skipTransition = useRef(true)

  useEffect(() => {
    const root = document.documentElement
    const animate = !skipTransition.current
    skipTransition.current = false

    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)',
    ).matches

    if (animate && !prefersReducedMotion) {
      root.classList.add(THEME_TRANSITION_CLASS)
    }

    root.dataset.theme = theme
    localStorage.setItem(STORAGE_KEY, theme)

    if (animate && !prefersReducedMotion) {
      const timeoutId = window.setTimeout(() => {
        root.classList.remove(THEME_TRANSITION_CLASS)
      }, THEME_TRANSITION_MS)
      return () => window.clearTimeout(timeoutId)
    }
  }, [theme])

  const toggleTheme = useCallback(() => {
    setTheme((t) => (t === 'light' ? 'dark' : 'light'))
  }, [])

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  )
}

export function useTheme() {
  const ctx = useContext(ThemeContext)
  if (!ctx) throw new Error('useTheme must be used within ThemeProvider')
  return ctx
}
