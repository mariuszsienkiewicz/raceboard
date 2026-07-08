import { ScrollText } from "lucide-react";
import { Link } from "react-router-dom";
import LegalPage, { type LegalSection } from "@/components/legal/LegalPage";

const LAST_UPDATED = "7 July 2026";

const SECTIONS: LegalSection[] = [
    {
        id: "acceptance",
        title: "Acceptance of terms",
        content: (
            <>
                <p>
                    By accessing or using Raceboard, you agree to these Terms of Use. If you do not agree
                    with any part of them, please do not use the service.
                </p>
                <p>
                    Raceboard is an independent portfolio project that aggregates running events in Poland
                    into one searchable calendar.
                </p>
            </>
        ),
    },
    {
        id: "the-service",
        title: "The service",
        content: (
            <>
                <p>
                    Raceboard collects race information from public sources, normalises it and makes it
                    searchable. We work to keep this information accurate, but we cannot guarantee that every
                    date, distance or location is complete or up to date.
                </p>
                <p>
                    Always confirm the details of an event with its official organiser before you register or
                    travel. Raceboard is not the organiser of any race listed on the site.
                </p>
            </>
        ),
    },
    {
        id: "accounts",
        title: "Your account",
        content: (
            <>
                <p>When you create an account, you agree to:</p>
                <ul>
                    <li>provide accurate registration information;</li>
                    <li>keep your password secure and not share your account;</li>
                    <li>take responsibility for activity that happens under your account.</li>
                </ul>
                <p>
                    We handle your account data as described in our{" "}
                    <Link to="/privacy">Privacy Policy</Link>.
                </p>
            </>
        ),
    },
    {
        id: "user-content",
        title: "Reviews and user content",
        content: (
            <>
                <p>
                    When you post reviews, you are responsible for what you write. You agree not to submit
                    content that is unlawful, misleading, offensive or that infringes the rights of others.
                </p>
                <p>
                    You keep ownership of your content, but you grant Raceboard the right to display it on the
                    platform. We may remove content that breaches these terms.
                </p>
            </>
        ),
    },
    {
        id: "acceptable-use",
        title: "Acceptable use",
        content: (
            <>
                <p>You agree not to:</p>
                <ul>
                    <li>disrupt, overload or attempt to break the service or its infrastructure;</li>
                    <li>scrape or copy the data at scale without permission;</li>
                    <li>use the service for any unlawful purpose.</li>
                </ul>
            </>
        ),
    },
    {
        id: "disclaimer",
        title: "Disclaimer and liability",
        content: (
            <>
                <p>
                    Raceboard is provided on an <strong>as is</strong> and <strong>as available</strong>{" "}
                    basis, without warranties of any kind. To the extent permitted by law, we are not liable
                    for any loss arising from your use of the service or from reliance on the information it
                    provides.
                </p>
            </>
        ),
    },
    {
        id: "changes",
        title: "Changes to these terms",
        content: (
            <>
                <p>
                    We may update these terms from time to time. When we do, we will update the date at the
                    top of this page. Continued use of the service after changes means you accept the updated
                    terms.
                </p>
            </>
        ),
    },
];

export default function TermsOfUsePage() {
    return (
        <LegalPage
            icon={ScrollText}
            badge="Terms"
            title="Terms of Use"
            seoDescription="Rules for using the Raceboard running calendar and your account."
            intro="The rules for using Raceboard, including your account, reviews and acceptable use of the service."
            lastUpdated={LAST_UPDATED}
            sections={SECTIONS}
        />
    );
}
