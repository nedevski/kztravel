import { countryLabels } from './strings'

export function formatLabel(value: string): string {
  return value
    .split('-')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

export function formatCountryLabel(country: string): string {
  return countryLabels[country] ?? formatLabel(country)
}

export function formatPrice(price: number): string {
  return `€${price.toLocaleString('bg-BG', { maximumFractionDigits: 0 })}`
}

export function formatDate(isoDate: string): string {
  const date = new Date(isoDate + 'T00:00:00')
  return date.toLocaleDateString('bg-BG', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

export function imageAltFromPath(path: string, fallback: string): string {
  const filename = path.split('/').pop()?.replace(/\.[^.]+$/, '') ?? fallback
  return filename.replace(/[-_]/g, ' ')
}
