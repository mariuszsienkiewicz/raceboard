package geocoder

import (
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
	"raceboard/geocoder/internal/domain"
	"time"
)

// Nominatim API result
// lat and lon are strings because Nominatim API returns them as strings
type nominatimResult struct {
	Lat string `json:"lat"`
	Lon string `json:"lon"`
}

type Client struct {
	httpClient *http.Client
	baseURL    string
}

func NewClient() *Client {
	return &Client{
		httpClient: &http.Client{Timeout: 10 * time.Second},
		baseURL:    "https://nominatim.openstreetmap.org/search",
	}
}

func (c *Client) Geocode(city string) (domain.Coordinates, error) {
	params := url.Values{}
	params.Set("q", city+", Poland")
	params.Set("format", "json")
	params.Set("limit", "1")

	endpoint := c.baseURL + "?" + params.Encode()

	req, err := http.NewRequest(http.MethodGet, endpoint, nil)
	if err != nil {
		return domain.Coordinates{}, fmt.Errorf("building request: %w", err)
	}

	req.Header.Set("User-Agent", "RaceBoard/1.0 (contact@heaps.pl)")

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return domain.Coordinates{}, fmt.Errorf("request failed: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return domain.Coordinates{}, fmt.Errorf("nominatim returned status %d", resp.StatusCode)
	}

	var results []nominatimResult
	if err := json.NewDecoder(resp.Body).Decode(&results); err != nil {
		return domain.Coordinates{}, fmt.Errorf("decoding response: %w", err)
	}

	if len(results) == 0 {
		return domain.Coordinates{}, fmt.Errorf("no results for city %q", city)
	}

	var lat, lng float64
	if _, err := fmt.Sscanf(results[0].Lat, "%g", &lat); err != nil {
		return domain.Coordinates{}, fmt.Errorf("parsing lat: %w", err)
	}
	if _, err := fmt.Sscanf(results[0].Lon, "%g", &lng); err != nil {
		return domain.Coordinates{}, fmt.Errorf("parsing lng: %w", err)
	}

	return domain.Coordinates{City: city, Lat: lat, Lng: lng}, nil
}
