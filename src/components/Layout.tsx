import { useEffect, type CSSProperties } from 'react'
import { Link, Outlet, useLocation } from 'react-router-dom'
import { ThemeToggle } from '@/components/ThemeToggle'
import { siteSettings } from '@/lib/loadData'

export function Layout() {
  const { pathname } = useLocation()

  useEffect(() => {
    window.scrollTo(0, 0)
  }, [pathname])

  useEffect(() => {
    const link =
      document.querySelector<HTMLLinkElement>("link[rel='icon']") ??
      document.createElement('link')
    link.rel = 'icon'
    link.href = siteSettings.favicon
    if (!link.parentNode) document.head.appendChild(link)
  }, [])

  const bgStyle = siteSettings.background
    ? ({ '--site-bg-image': `url(${siteSettings.background})` } as CSSProperties)
    : undefined

  return (
    <div className="site" style={bgStyle}>
      <header className="site-header">
        <div className="site-header__spacer" aria-hidden="true" />
        <Link to="/" className="site-header__brand">
          {siteSettings.title}
        </Link>
        <div className="site-header__actions">
          <ThemeToggle />
        </div>
      </header>
      <main className="site-main">
        <Outlet />
      </main>
      <footer className="site-footer">
        <p>&copy; {new Date().getFullYear()} {siteSettings.title}</p>
      </footer>
    </div>
  )
}
