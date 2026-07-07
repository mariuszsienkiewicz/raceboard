import { BrowserRouter, Route, Routes } from 'react-router-dom'
import LoginPage from './pages/LoginPage'
import HomePage from './pages/HomePage'
import { AuthProvider } from './context/AuthContext'
import Header from './components/Header'
import Footer from './components/Footer'
import RegistrationPage from './pages/RegistrationPage'
import RacePage from './pages/RacePage'
import WatchlistPage from './pages/WatchlistPage'
import AccountPage from './pages/AccountPage'
import AboutPage from './pages/AboutPage'
import PrivacyPolicyPage from './pages/legal/PrivacyPolicyPage'
import TermsOfUsePage from './pages/legal/TermsOfUsePage'
import CookiePolicyPage from './pages/legal/CookiePolicyPage'
import { ThemeProvider } from './components/ThemeProvider'
import { Toaster } from './components/ui/sonner'

function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <BrowserRouter>
          <div className="flex min-h-screen flex-col bg-background text-foreground">
            <Header />
            <main className="mx-auto w-full max-w-5xl flex-1 px-6 py-8">
              <Routes>
                <Route path="/" element={<HomePage />} />
                <Route path="/races/:id" element={<RacePage />} />
                <Route path="/login" element={<LoginPage />} />
                <Route path="/register" element={<RegistrationPage />} />
                <Route path="/watchlist" element={<WatchlistPage />} />
                <Route path="/account" element={<AccountPage />} />
                <Route path="/about" element={<AboutPage />} />
                <Route path="/privacy" element={<PrivacyPolicyPage />} />
                <Route path="/terms" element={<TermsOfUsePage />} />
                <Route path="/cookie" element={<CookiePolicyPage />} />
              </Routes>
            </main>
            <Footer />
            <Toaster richColors position="top-center" />
          </div>
        </BrowserRouter>
      </AuthProvider>
    </ThemeProvider>
  )
}

export default App
