import { Link, useLocation } from 'react-router-dom'
import { ui } from '@/lib/strings'

function isTripsRoute(pathname: string) {
  return pathname === '/' || pathname.startsWith('/trips/')
}

export function HeaderNav() {
  const { pathname } = useLocation()

  return (
    <nav className="site-header__nav" aria-label={ui.mainNav}>
      <Link
        to="/"
        className={`site-header__nav-link${isTripsRoute(pathname) ? ' site-header__nav-link--active' : ''}`}
      >
        {ui.navTrips}
      </Link>
      <Link
        to="/contact"
        className={`site-header__nav-link${pathname === '/contact' ? ' site-header__nav-link--active' : ''}`}
      >
        {ui.navContact}
      </Link>
      <Link
        to="/booking"
        className={`site-header__nav-link${pathname === '/booking' ? ' site-header__nav-link--active' : ''}`}
      >
        {ui.navBooking}
      </Link>
    </nav>
  )
}
