import { useEffect, useState, type CSSProperties } from 'react'
import { Link, Outlet, useLocation } from 'react-router-dom'
import { HeaderContact } from '@/components/HeaderContact'
import { HeaderNav } from '@/components/HeaderNav'
import { ThemeToggle } from '@/components/ThemeToggle'
import { siteSettings } from '@/lib/loadData'
import { ui } from '@/lib/strings'

export function Layout() {
  const { pathname } = useLocation()
  const [menuOpen, setMenuOpen] = useState(false)

  useEffect(() => {
    window.scrollTo(0, 0)
    setMenuOpen(false)
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
      <header className={`site-header${menuOpen ? ' site-header--menu-open' : ''}`}>
        <Link to="/" className="site-header__brand">
          {siteSettings.title}
        </Link>
        <div className="site-header__actions">
          <ThemeToggle />
          <button
            type="button"
            className="site-header__menu-toggle"
            onClick={() => setMenuOpen((open) => !open)}
            aria-expanded={menuOpen}
            aria-controls="site-header-menu"
            aria-label={menuOpen ? ui.closeMenu : ui.openMenu}
          >
            <svg className="site-header__menu-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              {menuOpen ? (
                <>
                  <path d="M6 6l12 12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                  <path d="M6 18L18 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                </>
              ) : (
                <>
                  <path d="M4 7h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                  <path d="M4 12h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                  <path d="M4 17h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                </>
              )}
            </svg>
          </button>
        </div>
        <div id="site-header-menu" className="site-header__menu">
          <HeaderNav />
          <div className="site-header__end">
            <HeaderContact />
            <ThemeToggle className="site-header__theme--desktop" />
          </div>
        </div>
      </header>
      <main className="site-main">
        <Outlet />
      </main>
      <footer className="site-footer">
        <p>
          Свидетелство за регистрация на туроператор номер РК-00-00000
          <br />
          ФИРМА ЕООД &copy; {new Date().getFullYear()} Всички права запазени.
        </p>
      </footer>
    </div>
  )
}
