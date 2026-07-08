import { Cookie } from "lucide-react";
import { Link } from "react-router-dom";
import LegalPage, { type LegalSection } from "@/components/legal/LegalPage";

const LAST_UPDATED = "7 July 2026";

const SECTIONS: LegalSection[] = [
    {
        id: "what-are-cookies",
        title: "What we store",
        content: (
            <>
                <p>
                    Cookies and similar technologies, such as local storage, are small pieces of data saved
                    in your browser. Raceboard uses only the minimum storage needed to make the site work and
                    to remember your preferences.
                </p>
                <p>We do not use advertising cookies or cross site tracking.</p>
            </>
        ),
    },
    {
        id: "essential",
        title: "Essential storage",
        content: (
            <>
                <p>These are required for core features and cannot be turned off:</p>
                <ul>
                    <li>
                        <strong>Authentication token:</strong> keeps you signed in after you log in, so you do
                        not have to enter your credentials on every page.
                    </li>
                    <li>
                        <strong>Session helpers:</strong> small values that support secure requests and keep
                        the app functioning correctly.
                    </li>
                </ul>
            </>
        ),
    },
    {
        id: "preferences",
        title: "Preference storage",
        content: (
            <>
                <p>These remember choices you make to improve your experience:</p>
                <ul>
                    <li>
                        <strong>Theme:</strong> remembers whether you prefer light or dark mode.
                    </li>
                    <li>
                        <strong>Search state:</strong> temporary values that help restore your scroll position
                        when you return to search results.
                    </li>
                </ul>
            </>
        ),
    },
    {
        id: "analytics",
        title: "Analytics and third parties",
        content: (
            <>
                <p>
                    Raceboard does not run third party advertising or profiling. Map tiles are loaded from an
                    external map provider when you view a race location, which may involve a request to that
                    provider. No personal profile is built from this.
                </p>
            </>
        ),
    },
    {
        id: "managing",
        title: "Managing storage",
        content: (
            <>
                <p>
                    You can clear cookies and local storage at any time through your browser settings. Note
                    that removing the authentication token will simply log you out, and clearing preferences
                    will reset things like your theme choice.
                </p>
                <p>
                    For more on how we handle your data, see our{" "}
                    <Link to="/privacy">Privacy Policy</Link>.
                </p>
            </>
        ),
    },
];

export default function CookiePolicyPage() {
    return (
        <LegalPage
            icon={Cookie}
            badge="Cookies"
            title="Cookie Policy"
            seoDescription="What cookies and local storage Raceboard uses and how you can manage them."
            intro="What Raceboard stores in your browser, why we store it and how you can manage it."
            lastUpdated={LAST_UPDATED}
            sections={SECTIONS}
        />
    );
}
