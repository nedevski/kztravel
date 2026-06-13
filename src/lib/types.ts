export interface ContactInfo {
  phone: string
  email: string
  address: string
  mapEmbedUrl: string
}

export interface SiteSettings {
  title: string
  favicon: string
  background?: string
  contact: ContactInfo
}

export type TripDateStatus = 'available' | 'soldout' | 'lastSpots'

export interface TripDate {
  date: string
  price: number
  priceBgn: number
  discountedPrice?: number
  discountedPriceBgn?: number
  status: TripDateStatus
}

export interface ItineraryDay {
  day: number
  title: string
  description: string
}

export interface InclusionItem {
  name: string
  price?: number
  priceBgn?: number
}

export interface TripYaml {
  name: string
  description: string
  country: string
  duration?: string
  category?: string[]
  thumbnails: string[]
  dates: TripDate[]
  gallery?: string[]
  itinerary?: ItineraryDay[]
  included?: InclusionItem[]
  excluded?: InclusionItem[]
}

export interface Trip extends Omit<TripYaml, 'category' | 'gallery' | 'itinerary' | 'included' | 'excluded'> {
  slug: string
  category: string[]
  gallery: string[]
  itinerary: ItineraryDay[]
  included: InclusionItem[]
  excluded: InclusionItem[]
  nextDate: TripDate | null
  displayPrice: number | null
  displayPriceBgn: number | null
  displayDiscountedPrice: number | null
  displayDiscountedPriceBgn: number | null
  fullyBooked: boolean
  moreAvailableDates: number
}

export interface PriceRange {
  id: string
  label: string
  min: number
  max: number | null
}

export interface DurationOption {
  days: string
  label: string
}

export interface TripFilters {
  country: string | null
  categories: string[]
  durations: string[]
  priceRange: PriceRange | null
}

export interface FilterIndex {
  countries: string[]
  categories: string[]
  durations: DurationOption[]
  priceRanges: PriceRange[]
  showCountryFilter: boolean
  showCategoryFilter: boolean
  showDurationFilter: boolean
  showPriceFilter: boolean
}
