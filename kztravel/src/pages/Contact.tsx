import { Link, useSearchParams } from 'react-router-dom'
import { PageTitle } from '@/components/PageTitle'
import { getTripBySlug, siteSettings } from '@/lib/loadData'
import { ui, weekdayLabels } from '@/lib/strings'

export function Contact() {
  const [searchParams] = useSearchParams()
  const tripSlug = searchParams.get('trip')
  const trip = tripSlug ? getTripBySlug(tripSlug) : undefined
  const { contact } = siteSettings

  const mailtoSubject = trip
    ? encodeURIComponent(ui.contactTripInquiry(trip.name))
    : encodeURIComponent(ui.contactHeading)

  return (
    <>
      <PageTitle title={ui.contactPageTitle} />
      <article className="contact">
        <Link to={trip ? `/trips/${trip.slug}` : '/'} className="contact__back">
          {trip ? `← ${trip.name}` : ui.allTrips}
        </Link>

        <header className="contact__header">
          <h1 className="contact__title">{ui.contactHeading}</h1>
          <p className="contact__intro">{ui.contactIntro}</p>
          {trip && (
            <p className="contact__trip">{ui.contactTripInquiry(trip.name)}</p>
          )}
          <p className="contact__booking-link">
            <Link to="/booking">{ui.contactBookingLink} →</Link>
          </p>
        </header>

        <section className="contact__details">
          <div className="contact__row">
            <div className="contact__item contact__card">
              <span className="contact__label">{ui.contactPhone}</span>
              <a
                className="contact__value"
                href={`tel:${contact.phone.replace(/\s/g, '')}`}
              >
                {contact.phone}
              </a>
            </div>
            <div className="contact__item contact__card">
              <span className="contact__label">{ui.contactEmail}</span>
              <a
                className="contact__value"
                href={`mailto:${contact.email}?subject=${mailtoSubject}`}
              >
                {contact.email}
              </a>
            </div>
          </div>
          <div className="contact__row">
            <div className="contact__item contact__card">
              <span className="contact__label">{ui.contactWorkingHours}</span>
              <dl className="contact__hours">
                {weekdayLabels.map((day, index) => (
                  <div key={day} className="contact__hours-row">
                    <dt>{day}</dt>
                    <dd>{contact.workingHours[index]}</dd>
                  </div>
                ))}
              </dl>
            </div>
            <div className="contact__item contact__card">
              <dl className="contact__bank">
                <div className="contact__bank-row">
                  <dt>{ui.contactBankName}</dt>
                  <dd>{contact.bankDetails.bankName}</dd>
                </div>
                <div className="contact__bank-row">
                  <dt>{ui.contactIban}</dt>
                  <dd>{contact.bankDetails.iban}</dd>
                </div>
                <div className="contact__bank-row">
                  <dt>{ui.contactAccountHolder}</dt>
                  <dd>{contact.bankDetails.holder}</dd>
                </div>
              </dl>
            </div>
          </div>
        </section>

        <section className="contact__map-section contact__card">
          <div className="contact__item">
            <span className="contact__label">{ui.contactOffice}</span>
            <p className="contact__map-label">{contact.address}</p>
          </div>
          <div className="contact__map">
            <iframe
              title={contact.address}
              src={contact.mapEmbedUrl}
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
            />
          </div>
        </section>
      </article>
    </>
  )
}
