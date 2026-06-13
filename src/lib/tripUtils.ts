import type { TripDate, TripYaml } from './types'

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

export function isFullyBooked(dates: TripDate[]): boolean {
  return getNextAvailableDate(dates) === null
}

export function enrichTrip(slug: string, data: TripYaml) {
  const upcomingBookable = getUpcomingBookableDates(data.dates)
  const nextDate = upcomingBookable[0] ?? null
  const ended = isTripEnded(data.dates)
  const lastDate = ended ? getMostRecentDate(data.dates) : null
  return {
    ...data,
    slug,
    category: data.category ?? [],
    gallery: data.gallery ?? [],
    itinerary: data.itinerary ?? [],
    included: data.included ?? [],
    excluded: data.excluded ?? [],
    nextDate,
    lastDate,
    displayPrice: nextDate?.price ?? lastDate?.price ?? null,
    displayPriceBgn: nextDate?.priceBgn ?? lastDate?.priceBgn ?? null,
    displayDiscountedPrice: nextDate?.discountedPrice ?? lastDate?.discountedPrice ?? null,
    displayDiscountedPriceBgn: nextDate?.discountedPriceBgn ?? lastDate?.discountedPriceBgn ?? null,
    ended,
    fullyBooked: !ended && nextDate === null,
    moreAvailableDates: getAdditionalBookableDateCount(data.dates),
  }
}
