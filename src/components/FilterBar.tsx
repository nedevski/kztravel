import { useMemo, useState } from 'react'
import { formatCountryLabel, formatLabel } from '@/lib/formatters'
import {
  filterTrips,
  getFilterPool,
  getTripFilterPrice,
  hasActiveFilters,
  priceInRange,
} from '@/lib/filters'
import { ui } from '@/lib/strings'
import type { FilterIndex, PriceRange, Trip, TripFilters } from '@/lib/types'

type FilterPanel = 'country' | 'price' | 'duration' | 'category'

interface FilterBarProps {
  filterIndex: FilterIndex
  trips: Trip[]
  filters: TripFilters
  onFiltersChange: (filters: TripFilters) => void
  onClear: () => void
}

function countByCountry(trips: Trip[], country: string): number {
  return trips.filter((trip) => trip.country === country).length
}

function countByCategory(trips: Trip[], category: string): number {
  return trips.filter((trip) => trip.category.includes(category)).length
}

function countByDuration(trips: Trip[], days: string): number {
  return trips.filter((trip) => {
    const match = trip.duration?.match(/(\d+)/)
    return match?.[1] === days
  }).length
}

function countByPriceRange(trips: Trip[], range: PriceRange): number {
  return trips.filter((trip) => {
    const price = getTripFilterPrice(trip)
    return price !== null && priceInRange(price, range)
  }).length
}

function triggerClass(isOpen: boolean, isActive: boolean, isDisabled: boolean): string {
  const classes = ['filter-box__trigger']
  if (isOpen) classes.push('filter-box__trigger--open')
  if (isActive) classes.push('filter-box__trigger--active')
  if (isDisabled) classes.push('filter-box__trigger--disabled')
  return classes.join(' ')
}

function chipClass(isSelected: boolean): string {
  return isSelected ? 'chip chip--active' : 'chip'
}

function FilterChevron({ open }: { open: boolean }) {
  return (
    <svg
      className={`filter-box__chevron${open ? ' filter-box__chevron--open' : ''}`}
      viewBox="0 0 16 16"
      fill="none"
      aria-hidden="true"
    >
      <path
        d="M4 6l4 4 4-4"
        stroke="currentColor"
        strokeWidth="1.75"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function FilterTrigger({
  label,
  isOpen,
  isActive,
  isDisabled,
  onClick,
}: {
  label: string
  isOpen: boolean
  isActive: boolean
  isDisabled: boolean
  onClick: () => void
}) {
  return (
    <button
      type="button"
      className={triggerClass(isOpen, isActive, isDisabled)}
      aria-expanded={isOpen}
      disabled={isDisabled}
      onClick={onClick}
    >
      <span>{label}</span>
      <FilterChevron open={isOpen} />
    </button>
  )
}

export function FilterBar({
  filterIndex,
  trips,
  filters,
  onFiltersChange,
  onClear,
}: FilterBarProps) {
  const [openPanel, setOpenPanel] = useState<FilterPanel | null>(null)

  const filteredCount = useMemo(() => filterTrips(trips, filters).length, [trips, filters])
  const isPoolEmpty = filteredCount === 0 && hasActiveFilters(filters)

  const countryPool = useMemo(
    () => getFilterPool(trips, filters, 'country'),
    [trips, filters],
  )
  const pricePool = useMemo(() => getFilterPool(trips, filters, 'price'), [trips, filters])
  const durationPool = useMemo(
    () => getFilterPool(trips, filters, 'duration'),
    [trips, filters],
  )
  const categoryPool = useMemo(
    () => getFilterPool(trips, filters, 'category'),
    [trips, filters],
  )

  const togglePanel = (panel: FilterPanel) => {
    if (isPoolEmpty) return
    setOpenPanel((current) => (current === panel ? null : panel))
  }

  const toggleCategory = (category: string) => {
    const categories = filters.categories.includes(category)
      ? filters.categories.filter((value) => value !== category)
      : [...filters.categories, category]
    onFiltersChange({ ...filters, categories })
  }

  const toggleDuration = (days: string) => {
    const durations = filters.durations.includes(days)
      ? filters.durations.filter((value) => value !== days)
      : [...filters.durations, days]
    onFiltersChange({ ...filters, durations })
  }

  const setCountry = (country: string | null) => {
    onFiltersChange({ ...filters, country })
  }

  const setPriceRange = (priceRange: PriceRange | null) => {
    onFiltersChange({ ...filters, priceRange })
  }

  const countryLabel =
    filters.country !== null
      ? `${ui.filterCountry}: ${formatCountryLabel(filters.country)}`
      : ui.filterCountry

  const priceLabel = filters.priceRange
    ? `${ui.filterPrice}: ${filters.priceRange.label}`
    : ui.filterPrice

  const durationLabel =
    filters.durations.length > 0
      ? `${ui.filterDuration} (${filters.durations.length})`
      : ui.filterDuration

  const categoryLabel =
    filters.categories.length > 0
      ? `${ui.filterCategory} (${filters.categories.length})`
      : ui.filterCategory

  return (
    <div
      className={`filter-box${isPoolEmpty ? ' filter-box--empty' : ''}`}
      aria-label={ui.filtersHeading}
    >
      <div className="filter-box__header">
        <h2 className="filter-box__title">{ui.filtersHeading}</h2>
      </div>

      <div className="filter-box__toolbar">
        <div className="filter-box__triggers">
          {filterIndex.showCountryFilter && (
            <FilterTrigger
              label={countryLabel}
              isOpen={openPanel === 'country'}
              isActive={filters.country !== null}
              isDisabled={isPoolEmpty}
              onClick={() => togglePanel('country')}
            />
          )}

          {filterIndex.showPriceFilter && (
            <FilterTrigger
              label={priceLabel}
              isOpen={openPanel === 'price'}
              isActive={filters.priceRange !== null}
              isDisabled={isPoolEmpty}
              onClick={() => togglePanel('price')}
            />
          )}

          {filterIndex.showDurationFilter && (
            <FilterTrigger
              label={durationLabel}
              isOpen={openPanel === 'duration'}
              isActive={filters.durations.length > 0}
              isDisabled={isPoolEmpty}
              onClick={() => togglePanel('duration')}
            />
          )}

          {filterIndex.showCategoryFilter && (
            <FilterTrigger
              label={categoryLabel}
              isOpen={openPanel === 'category'}
              isActive={filters.categories.length > 0}
              isDisabled={isPoolEmpty}
              onClick={() => togglePanel('category')}
            />
          )}
        </div>

        <button
          type="button"
          className="filter-box__clear"
          onClick={onClear}
          disabled={!hasActiveFilters(filters)}
        >
          {ui.clear}
        </button>
      </div>

      <div
        className={`filter-box__panel-wrap${openPanel ? ' filter-box__panel-wrap--open' : ''}`}
      >
        {openPanel === 'country' && !isPoolEmpty && (
          <div className="filter-box__panel" role="group" aria-label={ui.filterByCountry}>
            {countryPool.length > 0 && (
              <button
                type="button"
                className={chipClass(filters.country === null)}
                onClick={() => setCountry(null)}
              >
                {ui.all}
                <span className="chip__count">{countryPool.length}</span>
              </button>
            )}
            {filterIndex.countries.map((country) => {
              const count = countByCountry(countryPool, country)
              if (count === 0) return null
              return (
                <button
                  key={country}
                  type="button"
                  className={chipClass(filters.country === country)}
                  onClick={() => setCountry(country)}
                >
                  {formatCountryLabel(country)}
                  <span className="chip__count">{count}</span>
                </button>
              )
            })}
          </div>
        )}

        {openPanel === 'price' && !isPoolEmpty && (
          <div className="filter-box__panel" role="group" aria-label={ui.filterByPrice}>
            {pricePool.length > 0 && (
              <button
                type="button"
                className={chipClass(filters.priceRange === null)}
                onClick={() => setPriceRange(null)}
              >
                {ui.all}
                <span className="chip__count">{pricePool.length}</span>
              </button>
            )}
            {filterIndex.priceRanges.map((range) => {
              const count = countByPriceRange(pricePool, range)
              if (count === 0) return null
              return (
                <button
                  key={range.id}
                  type="button"
                  className={chipClass(filters.priceRange?.id === range.id)}
                  onClick={() => setPriceRange(range)}
                >
                  {range.label}
                  <span className="chip__count">{count}</span>
                </button>
              )
            })}
          </div>
        )}

        {openPanel === 'duration' && !isPoolEmpty && (
          <div className="filter-box__panel" role="group" aria-label={ui.filterByDuration}>
            {filterIndex.durations.map(({ days, label }) => {
              const count = countByDuration(durationPool, days)
              if (count === 0) return null
              return (
                <button
                  key={days}
                  type="button"
                  className={chipClass(filters.durations.includes(days))}
                  onClick={() => toggleDuration(days)}
                >
                  {label}
                  <span className="chip__count">{count}</span>
                </button>
              )
            })}
          </div>
        )}

        {openPanel === 'category' && !isPoolEmpty && (
          <div
            className="filter-box__panel filter-box__panel--category"
            role="group"
            aria-label={ui.filterByCategory}
          >
            {filterIndex.categories.map((category) => {
              const count = countByCategory(categoryPool, category)
              if (count === 0) return null
              return (
                <button
                  key={category}
                  type="button"
                  className={chipClass(filters.categories.includes(category))}
                  onClick={() => toggleCategory(category)}
                >
                  {formatLabel(category)}
                  <span className="chip__count">{count}</span>
                </button>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
