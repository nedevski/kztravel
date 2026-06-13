import { parse } from 'yaml'
import bookingYaml from '../../data/booking.yaml?raw'
import siteYaml from '../../data/site.yaml?raw'
import { buildFilterIndex } from './filters'
import { enrichTrip } from './tripUtils'
import type { BookingInfo, ContactInfo, SiteSettings, Trip, TripYaml } from './types'

const tripModules = import.meta.glob('../../data/trips/*.yaml', {
  eager: true,
  query: '?raw',
  import: 'default',
}) as Record<string, string>

function assertContactInfo(contact: ContactInfo): ContactInfo {
  if (
    !Array.isArray(contact.workingHours) ||
    contact.workingHours.length !== 7 ||
    contact.workingHours.some((hours) => !hours?.trim())
  ) {
    throw new Error(
      '[kztravelreact] site.yaml: contact.workingHours must be an array of 7 day entries',
    )
  }
  const { bankName, iban, holder } = contact.bankDetails ?? {}
  if (!bankName?.trim() || !iban?.trim() || !holder?.trim()) {
    throw new Error('[kztravelreact] site.yaml: contact.bankDetails (bankName, iban, holder) is required')
  }
  return contact
}

const parsedSite = parse(siteYaml) as SiteSettings
export const siteSettings: SiteSettings = {
  ...parsedSite,
  contact: assertContactInfo(parsedSite.contact),
}

export const bookingInfo: BookingInfo = parse(bookingYaml) as BookingInfo

export const trips: Trip[] = Object.entries(tripModules)
  .map(([path, content]) => {
    const slug = path.split('/').pop()!.replace('.yaml', '')
    const data = parse(content) as TripYaml
    return enrichTrip(slug, data)
  })
  .sort((a, b) => a.name.localeCompare(b.name))

export const filterIndex = buildFilterIndex(trips)

export function getTripBySlug(slug: string): Trip | undefined {
  return trips.find((t) => t.slug === slug)
}
