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

export interface TripDate {
  date: string
  price: number
  discountedPrice?: number
  available: boolean
}

export interface ItineraryDay {
  day: number
  title: string
  description: string
}

export interface InclusionItem {
  name: string
  price?: number
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
  displayDiscountedPrice: number | null
  fullyBooked: boolean
  moreAvailableDates: number
}

export interface FilterIndex {
  countries: string[]
}
