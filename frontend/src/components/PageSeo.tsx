import { useEffect } from "react";

const SITE_NAME = "Raceboard";

type PageSeoProps = {
    title: string;
    description?: string;
    noIndex?: boolean;
};

function upsertMeta(attr: "name" | "property", key: string, content: string) {
    let el = document.querySelector<HTMLMetaElement>(`meta[${attr}="${key}"]`);
    if (!el) {
        el = document.createElement("meta");
        el.setAttribute(attr, key);
        document.head.appendChild(el);
    }
    el.content = content;
}

function removeMeta(attr: "name" | "property", key: string) {
    document.querySelector<HTMLMetaElement>(`meta[${attr}="${key}"]`)?.remove();
}

export default function PageSeo({ title, description, noIndex }: PageSeoProps) {
    const fullTitle = title === SITE_NAME ? title : `${title} | ${SITE_NAME}`;

    useEffect(() => {
        document.title = fullTitle;

        if (description) {
            upsertMeta("name", "description", description);
            upsertMeta("property", "og:description", description);
        }

        upsertMeta("property", "og:title", fullTitle);
        upsertMeta("property", "og:type", "website");
        upsertMeta("property", "og:url", window.location.href);

        if (noIndex) {
            upsertMeta("name", "robots", "noindex, nofollow");
        } else {
            removeMeta("name", "robots");
        }
    }, [fullTitle, description, noIndex]);

    return null;
}
