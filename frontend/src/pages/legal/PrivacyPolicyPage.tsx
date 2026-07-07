import { ShieldCheck } from "lucide-react";
import { Link } from "react-router-dom";
import LegalPage, { type LegalSection } from "@/components/legal/LegalPage";

const LAST_UPDATED = "7 July 2026";

const SECTIONS: LegalSection[] = [
    {
        id: "overview",
        title: "Overview",
        content: (
            <>
                <p>
                    Raceboard is a running calendar that gathers running events from across Poland into one
                    searchable place. This Privacy Policy explains what personal data we collect, why we
                    collect it and how we handle it when you use the site.
                </p>
                <p>
                    Raceboard is an independent, portfolio project. We keep data collection to the minimum
                    needed to run the service and we never sell your data.
                </p>
            </>
        ),
    },
    {
        id: "data-we-collect",
        title: "Data we collect",
        content: (
            <>
                <p>We only collect what is needed to provide accounts and personalised features:</p>
                <ul>
                    <li>
                        <strong>Account data:</strong> your email address, display name and a securely hashed
                        password when you register.
                    </li>
                    <li>
                        <strong>Activity data:</strong> the races you save to your watchlist and the reviews you
                        publish.
                    </li>
                    <li>
                        <strong>Technical data:</strong> basic information needed to keep you logged in, such as
                        an authentication token stored in your browser.
                    </li>
                </ul>
                <p>
                    Race event information itself is aggregated from public sources and is not personal data.
                </p>
            </>
        ),
    },
    {
        id: "how-we-use-data",
        title: "How we use your data",
        content: (
            <>
                <p>We use your data to:</p>
                <ul>
                    <li>create and manage your account and keep you signed in;</li>
                    <li>store your watchlist and show it back to you across sessions;</li>
                    <li>publish your reviews next to your display name;</li>
                    <li>keep the service secure and prevent abuse.</li>
                </ul>
                <p>
                    We do not use your data for advertising and we do not run third party tracking or
                    analytics profiling.
                </p>
            </>
        ),
    },
    {
        id: "legal-basis",
        title: "Legal basis and retention",
        content: (
            <>
                <p>
                    Under the GDPR, we process your account data to perform the service you asked for and on
                    the basis of our legitimate interest in keeping the platform safe. We keep your data for
                    as long as your account exists.
                </p>
                <p>
                    When you delete your account, we remove your personal data, except where we are required
                    to keep limited records to comply with the law.
                </p>
            </>
        ),
    },
    {
        id: "your-rights",
        title: "Your rights",
        content: (
            <>
                <p>You have the right to:</p>
                <ul>
                    <li>access the personal data we hold about you;</li>
                    <li>correct inaccurate data, for example your display name;</li>
                    <li>request deletion of your account and associated data;</li>
                    <li>object to or restrict certain processing.</li>
                </ul>
                <p>
                    To exercise any of these rights, contact us using the details in the Contact section
                    below.
                </p>
            </>
        ),
    },
    {
        id: "cookies",
        title: "Cookies and local storage",
        content: (
            <>
                <p>
                    We use a small amount of browser storage to keep you logged in and to remember your
                    theme preference. We do not use advertising or cross site tracking cookies. For details,
                    see our <Link to="/cookie">Cookie Policy</Link>.
                </p>
            </>
        ),
    },
    {
        id: "contact",
        title: "Contact",
        content: (
            <>
                <p>
                    If you have questions about this policy or your data, reach out through the project
                    repository or the contact channel listed there. As a portfolio project, Raceboard aims to
                    respond to reasonable requests promptly.
                </p>
            </>
        ),
    },
];

export default function PrivacyPolicyPage() {
    return (
        <LegalPage
            icon={ShieldCheck}
            badge="Privacy"
            title="Privacy Policy"
            intro="How Raceboard collects, uses and protects your personal data when you use the site."
            lastUpdated={LAST_UPDATED}
            sections={SECTIONS}
        />
    );
}
