import { PriceDisplay } from '@/components/PriceDisplay'
import { formatDate } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import { isDatePast } from '@/lib/tripUtils'
import type { TripDate, TripDateStatus } from '@/lib/types'

interface DatesTableProps {
  dates: TripDate[]
}

const statusConfig: Record<
  TripDateStatus,
  { className: string; label: string }
> = {
  available: { className: 'status-chip--available', label: ui.available },
  lastSpots: { className: 'status-chip--lastSpots', label: ui.lastSpots },
  soldout: { className: 'status-chip--soldout', label: ui.soldOut },
}

function DateStatus({ date, status }: { date: string; status: TripDateStatus }) {
  if (isDatePast(date)) {
    return <span className="dates-table__completed">{ui.completed}</span>
  }

  return (
    <span className={`status-chip ${statusConfig[status].className}`}>
      {statusConfig[status].label}
    </span>
  )
}

function dateEntryClass(entry: TripDate) {
  if (isDatePast(entry.date)) return 'dates-table__past'
  if (entry.status === 'soldout') return 'dates-table__unavailable'
  return ''
}

export function DatesTable({ dates }: DatesTableProps) {
  const sorted = [...dates].sort((a, b) => a.date.localeCompare(b.date))

  return (
    <div className="dates-table">
      <table className="dates-table__desktop">
        <thead>
          <tr>
            <th>{ui.date}</th>
            <th>{ui.price}</th>
            <th>{ui.status}</th>
          </tr>
        </thead>
        <tbody>
          {sorted.map((entry) => (
            <tr key={entry.date} className={dateEntryClass(entry)}>
              <td>{formatDate(entry.date)}</td>
              <td>
                <PriceDisplay
                  price={entry.price}
                  priceBgn={entry.priceBgn}
                  discountedPrice={entry.discountedPrice}
                  discountedPriceBgn={entry.discountedPriceBgn}
                />
              </td>
              <td>
                <DateStatus date={entry.date} status={entry.status} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="dates-table__mobile">
        {sorted.map((entry) => (
          <div
            key={entry.date}
            className={`dates-table__card ${dateEntryClass(entry)}`}
          >
            <div className="dates-table__card-row">
              <div className="dates-table__card-date">{formatDate(entry.date)}</div>
              <div className="dates-table__card-price">
                <PriceDisplay
                  price={entry.price}
                  priceBgn={entry.priceBgn}
                  discountedPrice={entry.discountedPrice}
                  discountedPriceBgn={entry.discountedPriceBgn}
                />
              </div>
            </div>
            <div className="dates-table__card-status">
              <DateStatus date={entry.date} status={entry.status} />
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
