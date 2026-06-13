import { Link } from 'react-router-dom'
import { PageTitle } from '@/components/PageTitle'
import { bookingInfo } from '@/lib/loadData'
import { ui } from '@/lib/strings'

export function Booking() {
  return (
    <>
      <PageTitle title={bookingInfo.title} />
      <article className="booking">
        <Link to="/" className="booking__back">
          {ui.allTrips}
        </Link>

        <header className="booking__header">
          <h1 className="booking__title">{bookingInfo.title}</h1>
          <p className="booking__intro">{bookingInfo.intro}</p>
        </header>

        {bookingInfo.sections.map((section) => (
          <section key={section.title} className="booking__section">
            <h2 className="booking__section-title">{section.title}</h2>
            <ul className="booking__list">
              {section.items.map((item) => (
                <li key={item}>{item}</li>
              ))}
            </ul>
          </section>
        ))}

        <div className="booking__cta">
          <Link to="/contact" className="btn btn--primary">
            {ui.contactHeading}
          </Link>
        </div>
      </article>
    </>
  )
}
