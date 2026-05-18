// SplitFlapChar.tsx
import { useState, useEffect, useCallback, useRef } from "react";
import "./SplitFlapChar.css";

const CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ".split("");
const FLIP_DURATION = 150; // ms per single flip

function getRandomChar(exclude?: string): string {
  let ch: string;
  do {
    ch = CHARS[Math.floor(Math.random() * CHARS.length)];
  } while (ch === exclude);
  return ch;
}

export default function SplitFlapChar() {
  const [current, setCurrent] = useState("A");
  const [next, setNext] = useState("A");
  const [flipping, setFlipping] = useState(false);
  const timeoutRef = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

  const flipTo = useCallback(
    (target: string) => {
      if (flipping) return;

      const currentIndex = CHARS.indexOf(current);
      const targetIndex = CHARS.indexOf(target);
      if (currentIndex === targetIndex) return;

      // Walk through characters one by one (like a real split-flap)
      const steps: string[] = [];
      let i = currentIndex;
      while (i !== targetIndex) {
        i = (i + 1) % CHARS.length;
        steps.push(CHARS[i]);
      }

      setFlipping(true);

      let step = 0;
      const doStep = () => {
        setNext(steps[step]);
        // After animation completes, commit and maybe continue
        timeoutRef.current = setTimeout(() => {
          setCurrent(steps[step]);
          step++;
          if (step < steps.length) {
            doStep();
          } else {
            setFlipping(false);
          }
        }, FLIP_DURATION);
      };
      doStep();
    },
    [current, flipping]
  );

  // Auto-change every 2-4 seconds
  useEffect(() => {
    const id = setInterval(
      () => {
        const target = getRandomChar(current);
        flipTo(target);
      },
      2000 + Math.random() * 2000
    );
    return () => clearInterval(id);
  }, [current, flipTo]);

  // Cleanup
  useEffect(() => {
    return () => {
      if (timeoutRef.current) clearTimeout(timeoutRef.current);
    };
  }, []);

  const isAnimating = current !== next;

  return (
    <div className="split-flap">
      {/* ---- TOP HALF (static, shows next char when flipping) ---- */}
      <div className="half top">
        <div className="half-inner">
          <span className="char">{isAnimating ? next : current}</span>
        </div>
      </div>

      {/* ---- BOTTOM HALF (static, shows current char until flip finishes) ---- */}
      <div className="half bottom">
        <div className="half-inner">
          <span className="char">{current}</span>
        </div>
      </div>

      {/* ---- FLIPPING TOP FLAP (folds down, shows old char) ---- */}
      {isAnimating && (
        <div className="flap flap-top" key={`top-${next}`}>
          <div className="half-inner">
            <span className="char">{current}</span>
          </div>
        </div>
      )}

      {/* ---- FLIPPING BOTTOM FLAP (folds up, reveals new char) ---- */}
      {isAnimating && (
        <div className="flap flap-bottom" key={`bot-${next}`}>
          <div className="half-inner">
            <span className="char">{next}</span>
          </div>
        </div>
      )}

      {/* Center divider line */}
      <div className="divider" />
    </div>
  );
}
