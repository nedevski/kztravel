import type { Trip, TripDate, TripDateStatus, TripYaml } from './types'

const VALID_STATUSES: TripDateStatus[] = ['available', 'lastSpots', 'soldout']

export function normalizeDate(entry: TripDate): TripDate {
  if (entry.status && VALID_STATUSES.includes(entry.status)) return entry

  if (entry.status) {
    console.warn(
      `[kztravel] Unknown status "${entry.status}" for date ${entry.date}, defaulting to available`,
    )
  }

  return {
    ...entry,
    status: entry.available === false ? 'soldout' : 'available',
  }
}

export function getTodayISO(): string {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function isDatePast(date: string): boolean {
  return date < getTodayISO()
}

export function hasUpcomingDates(dates: TripDate[]): boolean {
  const today = getTodayISO()
  return dates.some((entry) => entry.date >= today)
}

export function isTripEnded(dates: TripDate[]): boolean {
  return dates.length > 0 && !hasUpcomingDates(dates)
}

export function getMostRecentDate(dates: TripDate[]): TripDate | null {
  if (dates.length === 0) return null
  return [...dates].sort((a, b) => b.date.localeCompare(a.date))[0]
}

export function isDateBookable(date: TripDate): boolean {
  switch (date.status) {
    case 'available':
    case 'lastSpots':
      return true
    case 'soldout':
      return false
    default:
      return date.available !== false
  }
}

export function getBookableDates(dates: TripDate[]): TripDate[] {
  return dates.filter(isDateBookable)
}

export function getUpcomingBookableDates(dates: TripDate[]): TripDate[] {
  const today = getTodayISO()
  return getBookableDates(dates)
    .filter((date) => date.date >= today)
    .sort((a, b) => a.date.localeCompare(b.date))
}

/** Additional upcoming non-sold-out dates beyond the one shown on the trip card. */
export function getAdditionalBookableDateCount(dates: TripDate[]): number {
  const upcoming = getUpcomingBookableDates(dates)
  return Math.max(0, upcoming.length - 1)
}

export function getNextAvailableDate(dates: TripDate[]): TripDate | null {
  return getUpcomingBookableDates(dates)[0] ?? null
}

/** Effective price for comparison — discounted when set, otherwise regular. */
export function getEffectiveDatePrice(date: TripDate): number {
  return date.discountedPrice ?? date.price
}

/**
 * Among upcoming bookable dates, pick the one with the lowest effective price.
 * e.g. turkey-istanbul: Apr €420, Sep €399 discounted → returns Sep date (€399).
 */
export function getLowestBookableDate(dates: TripDate[]): TripDate | null {
  const upcoming = getUpcomingBookableDates(dates)
  if (upcoming.length === 0) return null
  return upcoming.reduce((lowest, current) =>
    getEffectiveDatePrice(current) < getEffectiveDatePrice(lowest) ? current : lowest,
  )
}

export function tripHasActiveDiscount(trip: Pick<Trip, 'dates'>): boolean {
  return getUpcomingBookableDates(trip.dates).some(
    (date) =>
      date.discountedPrice != null &&
      date.discountedPrice > 0 &&
      date.discountedPrice < date.price,
  )
}

export function pickRandomTrips(trips: Trip[], count: number): Trip[] {
  const shuffled = [...trips]
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
  }
  return shuffled.slice(0, count)
}

export function isFullyBooked(dates: TripDate[]): boolean {
  return getNextAvailableDate(dates) === null
}

export function enrichTrip(slug: string, data: TripYaml): Trip {
  const dates = data.dates.map(normalizeDate)
  const upcomingBookable = getUpcomingBookableDates(dates)
  const nextDate = upcomingBookable[0] ?? null
  const lowestDate = getLowestBookableDate(dates)
  const ended = isTripEnded(dates)
  const lastDate = ended ? getMostRecentDate(dates) : null
  const priceDate = lowestDate ?? lastDate
  return {
    ...data,
    dates,
    slug,
    category: data.category ?? [],
    gallery: data.gallery ?? [],
    itinerary: data.itinerary ?? [],
    included: data.included ?? [],
    excluded: data.excluded ?? [],
    nextDate,
    lastDate,
    displayPrice: priceDate?.price ?? null,
    displayPriceBgn: priceDate?.priceBgn ?? null,
    displayDiscountedPrice: priceDate?.discountedPrice ?? null,
    displayDiscountedPriceBgn: priceDate?.discountedPriceBgn ?? null,
    ended,
    fullyBooked: !ended && nextDate === null,
    moreAvailableDates: getAdditionalBookableDateCount(dates),
  }
}
