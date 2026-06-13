import { useCallback, useMemo } from 'react'
import { useSearchParams } from 'react-router-dom'
import { FilterBar } from '@/components/FilterBar'
import { PageTitle } from '@/components/PageTitle'
import { TripCard } from '@/components/TripCard'
import {
  buildFilterSearch,
  emptyFilters,
  filterTrips,
  hasActiveFilters,
  parseFiltersFromSearch,
} from '@/lib/filters'
import { filterIndex, trips } from '@/lib/loadData'
import { ui } from '@/lib/strings'
import type { TripFilters } from '@/lib/types'

export function Home() {
  const [searchParams, setSearchParams] = useSearchParams()

  const filters = useMemo(
    () => parseFiltersFromSearch(searchParams.toString(), filterIndex.priceRanges),
    [searchParams],
  )

  const filteredTrips = useMemo(() => filterTrips(trips, filters), [filters])

  const updateFilters = useCallback(
    (nextFilters: TripFilters) => {
      const search = buildFilterSearch(nextFilters)
      setSearchParams(search ? new URLSearchParams(search.slice(1)) : {}, { replace: true })
    },
    [setSearchParams],
  )

  const handleClear = () => {
    updateFilters(emptyFilters)
  }

  return (
    <>
      <PageTitle />
      <section className="home">
        <div className="home__intro">
          <h1 className="home__heading">{ui.homeHeading}</h1>
          <p className="home__subheading">{ui.homeSubheading}</p>
        </div>

        <FilterBar
          filterIndex={filterIndex}
          trips={trips}
          filters={filters}
          onFiltersChange={updateFilters}
          onClear={handleClear}
        />

        {filteredTrips.length > 0 ? (
          <div className="trip-grid">
            {filteredTrips.map((trip) => (
              <TripCard key={trip.slug} trip={trip} />
            ))}
          </div>
        ) : (
          <div className="empty-state">
            <p>{ui.noTripsMatch}</p>
            {hasActiveFilters(filters) && (
              <button type="button" className="empty-state__btn" onClick={handleClear}>
                {ui.clearFilters}
              </button>
            )}
          </div>
        )}
      </section>
    </>
  )
}
