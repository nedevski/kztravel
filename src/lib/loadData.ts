import { parse } from 'yaml'
import siteYaml from '../../data/site.yaml?raw'
import { buildFilterIndex } from './filters'
import { enrichTrip } from './tripUtils'
import type { SiteSettings, Trip, TripYaml } from './types'

const tripModules = import.meta.glob('../../data/trips/*.yaml', {
  eager: true,
  query: '?raw',
  import: 'default',
}) as Record<string, string>

export const siteSettings: SiteSettings = parse(siteYaml) as SiteSettings

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
