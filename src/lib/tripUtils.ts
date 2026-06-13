import type { TripDate, TripYaml } from './types'

const TODAY = new Date().toISOString().slice(0, 10)

export function getUpcomingAvailableDates(dates: TripDate[]): TripDate[] {
  return dates
    .filter((d) => d.available && d.date >= TODAY)
    .sort((a, b) => a.date.localeCompare(b.date))
}

export function getNextAvailableDate(dates: TripDate[]): TripDate | null {
  return getUpcomingAvailableDates(dates)[0] ?? null
}

export function isFullyBooked(dates: TripDate[]): boolean {
  return getNextAvailableDate(dates) === null
}

export function enrichTrip(slug: string, data: TripYaml) {
  const upcomingAvailable = getUpcomingAvailableDates(data.dates)
  const nextDate = upcomingAvailable[0] ?? null
  return {
    ...data,
    slug,
    category: data.category ?? [],
    gallery: data.gallery ?? [],
    itinerary: data.itinerary ?? [],
    included: data.included ?? [],
    excluded: data.excluded ?? [],
    nextDate,
    displayPrice: nextDate?.price ?? null,
    displayDiscountedPrice: nextDate?.discountedPrice ?? null,
    fullyBooked: nextDate === null,
    moreAvailableDates: Math.max(0, upcomingAvailable.length - 1),
  }
}
