import { Link } from 'react-router-dom'
import { PriceDisplay } from '@/components/PriceDisplay'
import { formatCountryLabel, formatDate, formatLabel } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import type { Trip } from '@/lib/types'
import { Slideshow } from './Slideshow'

interface TripCardProps {
  trip: Trip
}

export function TripCard({ trip }: TripCardProps) {
  const visibleCategories = trip.category.slice(0, 2)

  return (
    <Link to={`/trips/${trip.slug}`} className="trip-card">
      <div className="trip-card__media">
        <Slideshow images={trip.thumbnails} alt={trip.name} className="trip-card__slideshow" />
        {trip.fullyBooked && (
          <span className="trip-card__badge trip-card__badge--soldout">{ui.fullyBooked}</span>
        )}
      </div>
      <div className="trip-card__body">
        <div className="trip-card__badges">
          <span className="badge badge--country">{formatCountryLabel(trip.country)}</span>
          {visibleCategories.map((cat) => (
            <span key={cat} className="badge badge--category">
              {formatLabel(cat)}
            </span>
          ))}
        </div>
        <h2 className="trip-card__title">{trip.name}</h2>
        <div className="trip-card__meta">
          <div className="trip-card__pricing">
            {trip.duration && <span className="trip-card__duration">{trip.duration}</span>}
            <span className="trip-card__price-chip">
              {trip.fullyBooked ? (
                ui.contactUs
              ) : (
                <PriceDisplay
                  price={trip.displayPrice!}
                  priceBgn={trip.displayPriceBgn!}
                  discountedPrice={trip.displayDiscountedPrice}
                  discountedPriceBgn={trip.displayDiscountedPriceBgn}
                  variant="chip"
                />
              )}
            </span>
          </div>
          {!trip.fullyBooked && (
            <div className="trip-card__date-group">
              {trip.moreAvailableDates > 0 && (
                <span className="trip-card__more">
                  {ui.moreDates(trip.moreAvailableDates)}
                </span>
              )}
              <span className="trip-card__date">{formatDate(trip.nextDate!.date)}</span>
            </div>
          )}
        </div>
      </div>
    </Link>
  )
}
