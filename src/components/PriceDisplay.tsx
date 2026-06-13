import { formatPriceFrom } from '@/lib/formatters'

interface PriceDisplayProps {
  price: number
  discountedPrice?: number | null
  className?: string
  variant?: 'default' | 'chip'
}

function formatPriceAmount(price: number): string {
  return price.toLocaleString('bg-BG', { maximumFractionDigits: 0 })
}

export function PriceDisplay({
  price,
  discountedPrice,
  className,
  variant = 'default',
}: PriceDisplayProps) {
  const hasDiscount =
    discountedPrice != null && discountedPrice > 0 && discountedPrice < price
  const displayClass = [
    'price-display',
    variant === 'chip' ? 'price-display--chip' : '',
    className,
  ]
    .filter(Boolean)
    .join(' ')

  if (hasDiscount) {
    return (
      <span className={displayClass}>
        <span className="price-display__current">{formatPriceFrom(discountedPrice)}</span>
        <span className="price-display__original price-display__struck">
          €{formatPriceAmount(price)}
        </span>
      </span>
    )
  }

  return <span className={displayClass}>{formatPriceFrom(price)}</span>
}
