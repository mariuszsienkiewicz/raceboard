import { TileLayer } from "react-leaflet";

const ATTRIBUTION =
    '&copy; Esri, HERE, Garmin, FAO, NOAA, USGS, &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';

const MAX_ZOOM = 16;

export default function MapTiles() {
    return (
        <>
            <TileLayer
                attribution={ATTRIBUTION}
                url="https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}"
                maxZoom={MAX_ZOOM}
            />
            <TileLayer
                url="https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Reference/MapServer/tile/{z}/{y}/{x}"
                maxZoom={MAX_ZOOM}
            />
        </>
    );
}
