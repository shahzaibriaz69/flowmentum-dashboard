<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GhlPipeline;
use App\Models\GhlPipelineStage;
use App\Models\GhlOpportunity;

class PipelineTestSeeder extends Seeder
{
    public function run()
    {
        $locationId = auth()->user()->location_id ?? 'test_location_123';

        // 1. Create Sample Pipeline
        $pipeline = GhlPipeline::create([
            'ghl_location_id' => $locationId,
            'ghl_pipeline_id' => 'pipe_01',
            'name' => 'Sales Pipeline',
        ]);

        // 2. Create Stages
        $stagesData = [
            ['id' => 'stage_01', 'name' => 'New Lead', 'pos' => 0],
            ['id' => 'stage_02', 'name' => 'Contacted', 'pos' => 1],
            ['id' => 'stage_03', 'name' => 'Proposal Sent', 'pos' => 2],
            ['id' => 'stage_04', 'name' => 'Closed Won', 'pos' => 3],
        ];

        foreach ($stagesData as $s) {
            GhlPipelineStage::create([
                'ghl_location_id' => $locationId,
                'ghl_pipeline_id' => 'pipe_01',
                'ghl_stage_id' => $s['id'],
                'name' => $s['name'],
                'position' => $s['pos'],
            ]);
        }

        // 3. Create Sample Opportunities
        GhlOpportunity::create([
            'ghl_location_id' => $locationId,
            'ghl_pipeline_id' => 'pipe_01',
            'ghl_stage_id' => 'stage_01',
            'ghl_opportunity_id' => 'opp_01',
            'name' => 'Website Redesign',
            'monetary_value' => 4500,
            'status' => 'open',
        ]);

        GhlOpportunity::create([
            'ghl_location_id' => $locationId,
            'ghl_pipeline_id' => 'pipe_01',
            'ghl_stage_id' => 'stage_02',
            'ghl_opportunity_id' => 'opp_02',
            'name' => 'App Development',
            'monetary_value' => 6500,
            'status' => 'open',
        ]);
    }
}