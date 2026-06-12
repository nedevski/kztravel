import { useCallback, useMemo } from 'react'
import { useSearchParams } from 'react-router-dom'
import { FilterBar } from '@/components/FilterBar'
import { PageTitle } from '@/components/PageTitle'
import { TripCard } from '@/components/TripCard'
import {
  buildFilterSearch,
  filterTrips,
  parseFiltersFromSearch,
} from '@/lib/filters'
import { filterIndex, trips } from '@/lib/loadData'
import { ui } from '@/lib/strings'

export function Home() {
  const [searchParams, setSearchParams] = useSearchParams()

  const { country } = useMemo(
    () => parseFiltersFromSearch(searchParams.toString()),
    [searchParams],
  )

  const filteredTrips = useMemo(() => filterTrips(trips, country), [country])

  const updateCountry = useCallback(
    (newCountry: string | null) => {
      const search = buildFilterSearch(newCountry)
      setSearchParams(search ? new URLSearchParams(search.slice(1)) : {}, { replace: true })
    },
    [setSearchParams],
  )

  const handleClear = () => {
    updateCountry(null)
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
          country={country}
          onCountryChange={updateCountry}
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
            <button type="button" className="empty-state__btn" onClick={handleClear}>
              {ui.clearFilters}
            </button>
          </div>
        )}
      </section>
    </>
  )
}
