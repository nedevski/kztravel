import { formatDualPrice, formatPrice } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import type { InclusionItem } from '@/lib/types'

interface InclusionListProps {
  items: InclusionItem[]
  variant: 'included' | 'excluded'
}

export function InclusionList({ items, variant }: InclusionListProps) {
  if (items.length === 0) return null

  return (
    <div className={`inclusion-table inclusion-table--${variant}`}>
      <table>
        <thead>
          <tr>
            <th>{ui.item}</th>
            <th>{ui.price}</th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={item.name}>
              <td>{item.name}</td>
              <td>
                {variant === 'included'
                  ? item.price == null || item.price === 0
                    ? ui.includedPrice
                    : formatPrice(item.price)
                  : item.price != null
                    ? item.priceBgn != null
                      ? formatDualPrice(item.price, item.priceBgn)
                      : formatPrice(item.price)
                    : ui.onRequest}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
