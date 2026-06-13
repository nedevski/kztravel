import { PriceDisplay } from '@/components/PriceDisplay'
import { formatDate } from '@/lib/formatters'
import { ui } from '@/lib/strings'
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

export function DatesTable({ dates }: DatesTableProps) {
  const sorted = [...dates].sort((a, b) => a.date.localeCompare(b.date))

  return (
    <div className="dates-table">
      <table>
        <thead>
          <tr>
            <th>{ui.date}</th>
            <th>{ui.price}</th>
            <th>{ui.status}</th>
          </tr>
        </thead>
        <tbody>
          {sorted.map((entry) => (
            <tr
              key={entry.date}
              className={entry.status === 'soldout' ? 'dates-table__unavailable' : ''}
            >
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
                <span
                  className={`status-chip ${statusConfig[entry.status].className}`}
                >
                  {statusConfig[entry.status].label}
                </span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
