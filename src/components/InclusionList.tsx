import { formatPrice } from '@/lib/formatters'
import { ui } from '@/lib/strings'
import type { InclusionItem } from '@/lib/types'

interface InclusionListProps {
  items: InclusionItem[]
  variant: 'included' | 'excluded'
}

export function InclusionList({ items, variant }: InclusionListProps) {
  if (items.length === 0) return null

  return (
    <ul className={`inclusion-list inclusion-list--${variant}`}>
      {items.map((item) => (
        <li key={item.name} className="inclusion-list__item">
          <span className="inclusion-list__name">{item.name}</span>
          <span className="inclusion-list__price">
            {variant === 'included'
              ? !item.price
                ? ui.includedPrice
                : formatPrice(item.price)
              : item.price
                ? formatPrice(item.price)
                : ui.onRequest}
          </span>
        </li>
      ))}
    </ul>
  )
}
