import type { ItineraryDay } from '@/lib/types'
import { ui } from '@/lib/strings'

interface ItineraryProps {
  days: ItineraryDay[]
}

export function Itinerary({ days }: ItineraryProps) {
  if (days.length === 0) return null

  const sorted = [...days].sort((a, b) => a.day - b.day)

  return (
    <ol className="itinerary">
      {sorted.map((day, i) => (
        <li key={`${day.day}-${i}`} className="itinerary__day">
          <span className="itinerary__number">{ui.day(day.day)}</span>
          <div className="itinerary__content">
            <h3 className="itinerary__title">{day.title}</h3>
            <p className="itinerary__description">{day.description}</p>
          </div>
        </li>
      ))}
    </ol>
  )
}
