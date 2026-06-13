import { formatDualPrice } from '@/lib/formatters'

interface PriceDisplayProps {
  price: number
  priceBgn: number
  discountedPrice?: number | null
  discountedPriceBgn?: number | null
  className?: string
  variant?: 'default' | 'chip'
}

export function PriceDisplay({
  price,
  priceBgn,
  discountedPrice,
  discountedPriceBgn,
  className,
  variant = 'default',
}: PriceDisplayProps) {
  const hasDiscount =
    discountedPrice != null &&
    discountedPriceBgn != null &&
    discountedPrice > 0 &&
    discountedPrice < price
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
        <span className="price-display__original price-display__struck">
          {formatDualPrice(price, priceBgn)}
        </span>
        <span className="price-display__current">
          {formatDualPrice(discountedPrice, discountedPriceBgn)}
        </span>
      </span>
    )
  }

  return <span className={displayClass}>{formatDualPrice(price, priceBgn)}</span>
}
