package main

import (
	"context"
	"encoding/json"
	"log"
	"net/http"
	"raceboard/geocoder/internal/cache"
	"raceboard/geocoder/internal/domain"
	"raceboard/geocoder/internal/geocoder"
	"time"
)

type GeocodeRequest struct {
	Cities []string `json:"cities"`
}

type GeocodeResponse struct {
	Results []domain.Coordinates `json:"coordinates"`
}

func geocodeHandler(w http.ResponseWriter, r *http.Request, service *geocoder.Service) {
	if r.Method != http.MethodPost {
		http.Error(w, "method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req GeocodeRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "invalid JSON", http.StatusBadRequest)
		return
	}

	ctx, cancel := context.WithTimeout(r.Context(), 90*time.Second)
	defer cancel()

	results := service.GeocodeMany(ctx, req.Cities)

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(GeocodeResponse{Results: results}); err != nil {
		log.Printf("failed to encode response: %v", err)
	}
}

func main() {
	client := geocoder.NewClient()
	geoCache := cache.New()
	service := geocoder.NewService(client, geoCache, 5)

	http.HandleFunc("/geocode", func(w http.ResponseWriter, r *http.Request) {
		geocodeHandler(w, r, service)
	})
	http.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
		_, _ = w.Write([]byte("ok"))
	})

	addr := ":8090"
	log.Printf("geocoder listening on %s", addr)
	if err := http.ListenAndServe(addr, nil); err != nil {
		log.Fatalf("server failed: %v", err)
	}
}
