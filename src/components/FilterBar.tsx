import { formatCountryLabel } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import type { FilterIndex, Trip } from '@/lib/types'

interface FilterBarProps {
  filterIndex: FilterIndex
  trips: Trip[]
  country: string | null
  onCountryChange: (country: string | null) => void
  onClear: () => void
}

function countByCountry(trips: Trip[], country: string): number {
  return trips.filter((t) => t.country === country).length
}

export function FilterBar({
  filterIndex,
  trips,
  country,
  onCountryChange,
  onClear,
}: FilterBarProps) {
  return (
    <div className="filter-bar">
      <div className="filter-bar__section">
        <span className="filter-bar__label">{ui.filterCountry}</span>
        <div className="filter-bar__chips" role="group" aria-label={ui.filterByCountry}>
          <button
            type="button"
            className={country === null ? 'chip chip--active' : 'chip'}
            onClick={() => onCountryChange(null)}
          >
            {ui.all}
            <span className="chip__count">{trips.length}</span>
          </button>
          {filterIndex.countries.map((c) => (
            <button
              key={c}
              type="button"
              className={country === c ? 'chip chip--active' : 'chip'}
              onClick={() => onCountryChange(c)}
            >
              {formatCountryLabel(c)}
              <span className="chip__count">{countByCountry(trips, c)}</span>
            </button>
          ))}
        </div>
      </div>

      {country !== null && (
        <button type="button" className="filter-bar__clear" onClick={onClear}>
          {ui.clearFilters}
        </button>
      )}
    </div>
  )
}
