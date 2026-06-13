import { siteSettings } from '@/lib/loadData'

function PhoneIcon() {
  return (
    <svg className="site-header__contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect
        x="7"
        y="3"
        width="10"
        height="18"
        rx="2"
        stroke="currentColor"
        strokeWidth="1.75"
      />
      <path d="M10 6h4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  )
}

function EmailIcon() {
  return (
    <svg className="site-header__contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect
        x="3"
        y="5"
        width="18"
        height="14"
        rx="2"
        stroke="currentColor"
        strokeWidth="1.75"
      />
      <path
        d="m4 7 8 6 8-6"
        stroke="currentColor"
        strokeWidth="1.75"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function LocationIcon() {
  return (
    <svg className="site-header__contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path
        d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z"
        stroke="currentColor"
        strokeWidth="1.75"
        strokeLinejoin="round"
      />
      <circle cx="12" cy="11" r="2.25" stroke="currentColor" strokeWidth="1.75" />
    </svg>
  )
}

export function HeaderContact() {
  const { contact } = siteSettings
  const phoneHref = `tel:${contact.phone.replace(/\s/g, '')}`

  return (
    <div className="site-header__contact">
      <a className="site-header__contact-item" href={phoneHref}>
        <PhoneIcon />
        <span>{contact.phone}</span>
      </a>
      <a className="site-header__contact-item" href={`mailto:${contact.email}`}>
        <EmailIcon />
        <span>{contact.email}</span>
      </a>
      <div className="site-header__contact-item site-header__contact-item--static">
        <LocationIcon />
        <span>{contact.address}</span>
      </div>
    </div>
  )
}
