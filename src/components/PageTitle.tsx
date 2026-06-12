import { useEffect } from 'react'
import { siteSettings } from '@/lib/loadData'

interface PageTitleProps {
  title?: string
}

export function PageTitle({ title }: PageTitleProps) {
  useEffect(() => {
    document.title = title ? `${title} — ${siteSettings.title}` : siteSettings.title
  }, [title])

  return null
}
