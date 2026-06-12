import type { FilterIndex, Trip } from './types'

export function buildFilterIndex(trips: Trip[]): FilterIndex {
  const countries = [...new Set(trips.map((t) => t.country))].sort()
  return { countries }
}

export function filterTrips(trips: Trip[], country: string | null): Trip[] {
  if (!country) return trips
  return trips.filter((trip) => trip.country === country)
}

export function parseFiltersFromSearch(search: string): { country: string | null } {
  const params = new URLSearchParams(search)
  return { country: params.get('country') }
}

export function buildFilterSearch(country: string | null): string {
  if (!country) return ''
  return `?country=${encodeURIComponent(country)}`
}
