import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { Layout } from '@/components/Layout'
import { Contact } from '@/pages/Contact'
import { Home } from '@/pages/Home'
import { TripDetail } from '@/pages/TripDetail'
import { ThemeProvider } from '@/lib/theme'

export default function App() {
  return (
    <ThemeProvider>
      <BrowserRouter basename={import.meta.env.BASE_URL.replace(/\/$/, '')}>
        <Routes>
          <Route element={<Layout />}>
            <Route index element={<Home />} />
            <Route path="trips/:slug" element={<TripDetail />} />
            <Route path="contact" element={<Contact />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </ThemeProvider>
  )
}
