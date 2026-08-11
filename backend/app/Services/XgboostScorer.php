<?php

namespace App\Services;

use RuntimeException;

class XgboostScorer
{
    /** @var array{trees: array, base_score: float|array, num_class: int} */
    private array $model;

    private function __construct(string $jsonPath)
    {
        if (!is_file($jsonPath)) {
            throw new RuntimeException("Model xgboost tidak ditemukan: {$jsonPath}");
        }
        $raw = json_decode((string) file_get_contents($jsonPath), true);
        if (!is_array($raw) || !isset($raw[0])) {
            throw new RuntimeException("Model xgboost rusak: {$jsonPath}");
        }
        $this->model = ['trees' => $raw, 'base_score' => 0.0, 'num_class' => 1];
    }

    public static function fromMeta(string $jsonPath, float|array $baseScore, int $numClass): self
    {
        $s = new self($jsonPath);
        $s->model['base_score'] = $baseScore;
        $s->model['num_class'] = $numClass;
        return $s;
    }

    private function scoreTree(array $node, array $features): float
    {
        if (isset($node['leaf'])) {
            return (float) $node['leaf'];
        }
        $value = $features[$node['split']] ?? null;
        if ($value === null) {
            $target = $node['missing'];
            foreach ($node['children'] as $child) {
                if ($child['nodeid'] === $target) {
                    return $this->scoreTree($child, $features);
                }
            }
        }
        return $value < $node['split_condition']
            ? $this->scoreTree($node['children'][0], $features)
            : $this->scoreTree($node['children'][1], $features);
    }

    /** Raw margin tunggal (regresi) atau array margin per kelas (multiclass). */
    public function raw(array $features): float|array
    {
        $base = $this->model['base_score'];
        $numClass = $this->model['num_class'];
        if ($numClass === 1) {
            $margin = (float) $base;
            foreach ($this->model['trees'] as $tree) {
                $margin += $this->scoreTree($tree, $features);
            }
            return $margin;
        }
        $margins = is_array($base) ? array_values($base) : array_fill(0, $numClass, (float) $base);
        $step = $numClass;
        $nRounds = intdiv(count($this->model['trees']), $step);
        for ($c = 0; $c < $numClass; $c++) {
            for ($r = 0; $r < $nRounds; $r++) {
                $margins[$c] += $this->scoreTree($this->model['trees'][$r * $step + $c], $features);
            }
        }
        return $margins;
    }

    /** Probabilitas per kelas (softmax) untuk multiclass. */
    public function predict(array $features): array
    {
        $raw = $this->raw($features);
        if (!is_array($raw)) {
            return [$raw];
        }
        $max = max($raw);
        $exp = array_map(fn ($m) => exp($m - $max), $raw);
        $sum = array_sum($exp);
        return array_map(fn ($e) => $e / $sum, $exp);
    }
}
