import { formatDate, formatPrice } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import type { TripDate } from '@/lib/types'

interface DatesTableProps {
  dates: TripDate[]
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
            <tr key={entry.date} className={entry.available ? '' : 'dates-table__unavailable'}>
              <td>{formatDate(entry.date)}</td>
              <td>{formatPrice(entry.price)}</td>
              <td>
                {entry.available ? (
                  <span className="dates-table__status dates-table__status--available">
                    {ui.available}
                  </span>
                ) : (
                  <span className="dates-table__status dates-table__status--soldout">
                    {ui.soldOut}
                  </span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
