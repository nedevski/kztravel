import { Link, Navigate, useParams } from 'react-router-dom'
import { DatesTable } from '@/components/DatesTable'
import { Gallery } from '@/components/Gallery'
import { InclusionList } from '@/components/InclusionList'
import { Itinerary } from '@/components/Itinerary'
import { PageTitle } from '@/components/PageTitle'
import { Slideshow } from '@/components/Slideshow'
import { formatCountryLabel, formatLabel, formatPrice } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import { buildFilterSearch } from '@/lib/filters'
import { getTripBySlug } from '@/lib/loadData'

export function TripDetail() {
  const { slug } = useParams<{ slug: string }>()
  const trip = slug ? getTripBySlug(slug) : undefined

  if (!trip) {
    return <Navigate to="/" replace />
  }

  return (
    <>
      <PageTitle title={trip.name} />
      <article className="trip-detail">
        <Link to="/" className="trip-detail__back">
          {ui.allTrips}
        </Link>

        <header className="trip-detail__hero">
          <Slideshow
            images={trip.thumbnails}
            alt={trip.name}
            className="trip-detail__slideshow"
          />
          <Link
            to={`/contact?trip=${trip.slug}`}
            className="btn btn--primary trip-detail__cta"
          >
            {ui.bookNow}
          </Link>
          <div className="trip-detail__hero-content">
            <div className="trip-detail__badges">
              <Link
                to={`/${buildFilterSearch(trip.country)}`}
                className="badge badge--country badge--link"
              >
                {formatCountryLabel(trip.country)}
              </Link>
              {trip.category.map((cat) => (
                <span key={cat} className="badge badge--category">
                  {formatLabel(cat)}
                </span>
              ))}
            </div>
            <h1 className="trip-detail__title">{trip.name}</h1>
            {trip.duration && <p className="trip-detail__duration">{trip.duration}</p>}
            <p className="trip-detail__description">{trip.description}</p>
            <div className="trip-detail__summary">
              {trip.fullyBooked ? (
                <span className="trip-detail__price">{ui.contactForAvailability}</span>
              ) : (
                <span className="trip-detail__price">
                  {ui.priceFrom(formatPrice(trip.displayPrice!))}
                </span>
              )}
            </div>
          </div>
        </header>

        <section className="trip-detail__section">
          <h2>{ui.datesAndPricing}</h2>
          <DatesTable dates={trip.dates} />
        </section>

        {trip.gallery.length > 0 && (
          <section className="trip-detail__section">
            <h2>{ui.gallery}</h2>
            <Gallery images={trip.gallery} tripName={trip.name} />
          </section>
        )}

        {trip.itinerary.length > 0 && (
          <section className="trip-detail__section">
            <h2>{ui.itinerary}</h2>
            <Itinerary days={trip.itinerary} />
          </section>
        )}

        {trip.included.length > 0 && (
          <section className="trip-detail__section">
            <h2>{ui.included}</h2>
            <InclusionList items={trip.included} variant="included" />
          </section>
        )}

        {trip.excluded.length > 0 && (
          <section className="trip-detail__section">
            <h2>{ui.notIncluded}</h2>
            <InclusionList items={trip.excluded} variant="excluded" />
          </section>
        )}
      </article>
    </>
  )
}
