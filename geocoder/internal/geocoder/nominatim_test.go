package geocoder

import (
	"net/http"
	"net/http/httptest"
	"testing"
)

func TestClient_Geocode(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte(`[{"lat":"52.23","lon":"21.01"}]`))
	}))
	defer server.Close()

	client := &Client{
		httpClient: server.Client(),
		baseURL:    server.URL,
	}

	coords, err := client.Geocode("Warszawa")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if coords.Lat != 52.23 {
		t.Errorf("lat: got %v, want 52.23", coords.Lat)
	}
	if coords.Lng != 21.01 {
		t.Errorf("lng: got %v, want 21.01", coords.Lng)
	}
	if coords.City != "Warszawa" {
		t.Errorf("city: got %q, want Warszawa", coords.City)
	}
}

func TestClient_Geocode_NoResults(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte(`[]`))
	}))
	defer server.Close()

	client := &Client{
		httpClient: server.Client(),
		baseURL:    server.URL,
	}

	_, err := client.Geocode("Nonexistent")

	if err == nil {
		t.Errorf("expected an error for empty results, got nil")
	}
}

func TestClient_Geocode_RateLimited(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusTooManyRequests)
	}))
	defer server.Close()

	client := &Client{
		httpClient: server.Client(),
		baseURL:    server.URL,
	}

	_, err := client.Geocode("Warszawa")

	if err == nil {
		t.Errorf("expected an error for HTTP 429, got nil")
	}
}
