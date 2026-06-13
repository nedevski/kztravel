import { formatLabel, formatPrice } from './formatters'
import { tripHasActiveDiscount } from './tripUtils'
import type { FilterIndex, PriceRange, Trip, TripFilters } from './types'

export function getTripFilterPrice(trip: Trip): number | null {
  if (trip.displayPrice === null) return null
  return trip.displayDiscountedPrice ?? trip.displayPrice
}

export function parseDurationDays(duration?: string): number | null {
  if (!duration) return null
  const match = duration.match(/(\d+)/)
  return match ? Number.parseInt(match[1], 10) : null
}

function buildPriceRanges(trips: Trip[]): PriceRange[] {
  const prices = trips
    .map(getTripFilterPrice)
    .filter((price): price is number => price !== null)

  if (prices.length === 0) return []

  const candidates: Omit<PriceRange, 'label'>[] = [
    { id: 'under-400', min: 0, max: 400 },
    { id: '400-600', min: 400, max: 600 },
    { id: '600-900', min: 600, max: 900 },
    { id: 'over-900', min: 900, max: null },
  ]

  return candidates
    .filter((range) => prices.some((price) => priceInRange(price, range as PriceRange)))
    .map((range) => ({
      ...range,
      label: formatPriceRangeLabel(range.min, range.max),
    }))
}

function formatPriceRangeLabel(min: number, max: number | null): string {
  if (max === null) return `над ${formatPrice(min)}`
  if (min === 0) return `до ${formatPrice(max)}`
  return `${formatPrice(min)} – ${formatPrice(max)}`
}

export function priceInRange(price: number, range: PriceRange): boolean {
  switch (range.id) {
    case 'under-400':
      return price < 400
    case '400-600':
      return price >= 400 && price <= 600
    case '600-900':
      return price > 600 && price <= 900
    case 'over-900':
      return price > 900
    default:
      if (range.max === null) return price >= range.min
      return price >= range.min && price <= range.max
  }
}

export function buildFilterIndex(trips: Trip[]): FilterIndex {
  const countries = [...new Set(trips.map((trip) => trip.country))].sort()

  const categories = [...new Set(trips.flatMap((trip) => trip.category))].sort((a, b) =>
    formatLabel(a).localeCompare(formatLabel(b), 'bg'),
  )

  const durationMap = new Map<number, string>()
  for (const trip of trips) {
    const days = parseDurationDays(trip.duration)
    if (days !== null && trip.duration) {
      durationMap.set(days, trip.duration)
    }
  }
  const durations = [...durationMap.entries()]
    .sort(([left], [right]) => left - right)
    .map(([days, label]) => ({ days: days.toString(), label }))

  const priceRanges = buildPriceRanges(trips)

  return {
    countries,
    categories,
    durations,
    priceRanges,
    showCountryFilter: countries.length > 1,
    showCategoryFilter: categories.length > 0,
    showDurationFilter: durations.length > 1,
    showPriceFilter: priceRanges.length > 1,
    showDiscountFilter: trips.some(tripHasActiveDiscount),
  }
}

export type FilterDimension = 'country' | 'price' | 'duration' | 'category' | 'discount'

export function getFilterPool(
  trips: Trip[],
  filters: TripFilters,
  dimension: FilterDimension,
): Trip[] {
  const partial: TripFilters = { ...filters }

  switch (dimension) {
    case 'country':
      partial.country = null
      break
    case 'price':
      partial.priceRange = null
      break
    case 'duration':
      partial.durations = []
      break
    case 'category':
      partial.categories = []
      break
    case 'discount':
      partial.discountedOnly = false
      break
  }

  return filterTrips(trips, partial)
}

export function filterTrips(trips: Trip[], filters: TripFilters): Trip[] {
  return trips.filter((trip) => {
    if (filters.country && trip.country !== filters.country) return false

    if (
      filters.categories.length > 0 &&
      !filters.categories.some((category) => trip.category.includes(category))
    ) {
      return false
    }

    if (filters.durations.length > 0) {
      const days = parseDurationDays(trip.duration)?.toString()
      if (!days || !filters.durations.includes(days)) return false
    }

    if (filters.priceRange) {
      const price = getTripFilterPrice(trip)
      if (price === null) return false
      if (!priceInRange(price, filters.priceRange)) return false
    }

    if (filters.discountedOnly && !tripHasActiveDiscount(trip)) return false

    return true
  })
}

export function parseFiltersFromSearch(
  search: string,
  priceRanges: PriceRange[] = [],
): TripFilters {
  const params = new URLSearchParams(search)
  const country = params.get('country')
  const categories = params.get('category')?.split(',').filter(Boolean) ?? []
  const durations = params.get('duration')?.split(',').filter(Boolean) ?? []
  const priceId = params.get('price')
  const priceRange = priceId
    ? (priceRanges.find((range) => range.id === priceId) ?? null)
    : null
  const discountedOnly = params.get('discount') === '1'

  return {
    country,
    categories,
    durations,
    priceRange,
    discountedOnly,
  }
}

export function buildFilterParams(filters: Partial<TripFilters>): URLSearchParams {
  const params = new URLSearchParams()

  if (filters.country) {
    params.set('country', filters.country)
  }

  if (filters.categories?.length) {
    params.set('category', filters.categories.join(','))
  }

  if (filters.durations?.length) {
    params.set('duration', filters.durations.join(','))
  }

  if (filters.priceRange) {
    params.set('price', filters.priceRange.id)
  }

  if (filters.discountedOnly) {
    params.set('discount', '1')
  }

  return params
}

export function buildFilterSearch(filters: Partial<TripFilters>): string {
  const search = buildFilterParams(filters).toString()
  return search ? `?${search}` : ''
}

export function hasActiveFilters(filters: TripFilters): boolean {
  return (
    filters.country !== null ||
    filters.categories.length > 0 ||
    filters.durations.length > 0 ||
    filters.priceRange !== null ||
    filters.discountedOnly
  )
}

export const emptyFilters: TripFilters = {
  country: null,
  categories: [],
  durations: [],
  priceRange: null,
  discountedOnly: false,
}
