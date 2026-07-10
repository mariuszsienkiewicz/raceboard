package geocoder

import (
	"context"
	"log"
	"raceboard/geocoder/internal/domain"
	"sync"
	"time"

	"golang.org/x/time/rate"
)

type Cache interface {
	Get(city string) (domain.Coordinates, bool)
	Set(city string, coords domain.Coordinates)
}

type Geocoder interface {
	Geocode(city string) (domain.Coordinates, error)
}

type Service struct {
	client  Geocoder
	cache   Cache
	workers int
	limiter *rate.Limiter
}

func NewService(client Geocoder, cache Cache, workers int) *Service {
	return &Service{
		client:  client,
		cache:   cache,
		workers: workers,
		limiter: rate.NewLimiter(rate.Every(time.Second), 1),
	}
}

func (s *Service) GeocodeMany(ctx context.Context, cities []string) []domain.Coordinates {
	jobs := make(chan string)
	results := make(chan domain.Coordinates)

	// waitgroup for workers
	var wg sync.WaitGroup

	for i := 0; i < s.workers; i++ {
		wg.Add(1)
		go s.worker(ctx, jobs, results, &wg)
	}

	go func() {
		defer close(jobs)
		for _, city := range cities {
			select {
			case jobs <- city:
			case <-ctx.Done():
				return
			}
		}
	}()

	// wait for all workers to finish
	go func() {
		wg.Wait()
		close(results)
	}()

	// collect results from results channel
	collected := make([]domain.Coordinates, 0, len(cities))
	for coords := range results {
		collected = append(collected, coords)
	}

	return collected
}

func (s *Service) worker(ctx context.Context, jobs <-chan string, results chan<- domain.Coordinates, wg *sync.WaitGroup) {
	defer wg.Done()

	for city := range jobs {
		if coords, found := s.cache.Get(city); found {
			log.Printf("cache hit for %q", city)
			select {
			case results <- coords:
			case <-ctx.Done():
				return
			}
			continue
		}

		if err := s.limiter.Wait(ctx); err != nil {
			log.Printf("rate limiter wait failed: %v", err)
			continue
		}

		coords, err := s.client.Geocode(city)
		if err != nil {
			log.Printf("geocoding %q failed: %v", city, err)
			continue
		}

		s.cache.Set(city, coords)
		select {
		case results <- coords:
		case <-ctx.Done():
			return
		}
	}
}
