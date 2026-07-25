package main

import (
	"context"
	"encoding/json"
	"errors"
	"log"
	"net/http"
	"os"
	"os/signal"
	"raceboard/geocoder/internal/cache"
	"raceboard/geocoder/internal/domain"
	"raceboard/geocoder/internal/geocoder"
	"syscall"
	"time"

	"github.com/prometheus/client_golang/prometheus"
	"github.com/prometheus/client_golang/prometheus/promauto"
	"github.com/prometheus/client_golang/prometheus/promhttp"
)

var (
	geocodeRequests = promauto.NewCounter(prometheus.CounterOpts{
		Name: "geocode_requests_total",
		Help: "Total number of /geocode requests.",
	})

	nominatimErrors = promauto.NewCounter(prometheus.CounterOpts{
		Name: "nominatim_errors_total",
		Help: "Total number of Nominatim errors.",
	})

	cacheHits = promauto.NewCounter(prometheus.CounterOpts{
		Name: "cache_hits_total",
		Help: "Total number of cache hits.",
	})

	cacheMisses = promauto.NewCounter(prometheus.CounterOpts{
		Name: "cache_misses_total",
		Help: "Total number of cache misses.",
	})

	geocodeInFlight = promauto.NewGauge(prometheus.GaugeOpts{
		Name: "geocode_in_flight",
		Help: "Number of /geocode requests currently being processed.",
	})

	geocodeDuration = promauto.NewHistogram(prometheus.HistogramOpts{
		Name:    "geocode_duration_seconds",
		Help:    "Duration of geocoding in seconds.",
		Buckets: []float64{0.5, 1, 2, 5, 10, 20, 30, 60},
	})
)

type prometheusMetrics struct{}

func (prometheusMetrics) RecordCacheHit() {
	cacheHits.Inc()
}

func (prometheusMetrics) RecordCacheMiss() {
	cacheMisses.Inc()
}

func (prometheusMetrics) RecordNominatimError() {
	nominatimErrors.Inc()
}

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
	service := geocoder.NewService(client, geoCache, &prometheusMetrics{}, 5)

	// geocode endpoint
	http.HandleFunc("/geocode", func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()
		geocodeRequests.Inc()
		geocodeInFlight.Inc()
		defer geocodeInFlight.Dec()
		defer func() {
			geocodeDuration.Observe(time.Since(start).Seconds())
		}()
		geocodeHandler(w, r, service)
	})

	// health endpoint
	http.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
		_, _ = w.Write([]byte("ok"))
	})

	// metrics endpoint
	http.Handle("/metrics", promhttp.Handler())

	ctx, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()

	addr := ":8090"
	server := &http.Server{
		Addr:    addr,
		Handler: nil,
	}

	go func() {
		log.Printf("geocoder listening on %s", addr)
		if err := server.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
			log.Fatalf("server failed: %v", err)
		}
	}()

	<-ctx.Done()
	log.Println("shutting down...")

	shutdownCtx, cancel := context.WithTimeout(context.Background(), 25*time.Second)
	defer cancel()

	if err := server.Shutdown(shutdownCtx); err != nil {
		log.Printf("graceful shutdown failed: %v", err)
	}
}
