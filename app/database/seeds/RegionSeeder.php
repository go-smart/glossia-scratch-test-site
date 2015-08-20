<?php
/**
 * This file is part of the Go-Smart Simulation Architecture (GSSA).
 * Go-Smart is an EU-FP7 project, funded by the European Commission.
 *
 * Copyright (C) 2013-  NUMA Engineering Ltd. (see AUTHORS file)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


use \Seeder;

use \Region;

class RegionSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    Region::create(['Name' => 'organ', 'Format' => 'zone', 'Groups' => '["boundary", "no-flux", "tissues", "organs"]', 'Segmentation' => ''])
      ->addSegmentationType(SegmentationTypeEnum::Liver)
      ->addSegmentationType(SegmentationTypeEnum::Lung)
      ->addSegmentationType(SegmentationTypeEnum::Kidney)
      ->addSegmentationType(SegmentationTypeEnum::Prostate);

    Region::create(['Name' => 'veins', 'Format' => 'surface', 'Groups' => '["vessels", "veins"]', 'Segmentation' => '']);
    Region::create(['Name' => 'arteries', 'Format' => 'surface', 'Groups' => '["vessels", "arteries"]', 'Segmentation' => '']);
    Region::create(['Name' => 'vessels', 'Format' => 'surface', 'Groups' => '["vessels"]', 'Segmentation' => '']) /* Generic vessels - could be veins/arteries */
      ->addSegmentationType(SegmentationTypeEnum::Vessels);

    Region::create(['Name' => 'tumour', 'Format' => 'zone', 'Groups' => '["tumours"]', 'Segmentation' => ''])
      ->addSegmentationType(SegmentationTypeEnum::Tumor);

    Region::create(['Name' => 'bronchi', 'Format' => 'surface', 'Groups' => '["bronchi"]', 'Segmentation' => ''])
      ->addSegmentationType(SegmentationTypeEnum::Bronchi);

    Region::create(['Name' => 'segmented-lesion', 'Format' => 'zone', 'Groups' => '["segmented-lesions"]', 'Segmentation' => ''])
      ->addSegmentationType(SegmentationTypeEnum::Lesion);

    Region::create(['Name' => 'existing-lesion', 'Format' => 'zone', 'Groups' => '["lesions"]', 'Segmentation' => ''])
      ->addSegmentationType(SegmentationTypeEnum::Simulation);

    Region::create(['Name' => 'tace', 'Format' => 'zone', 'Groups' => '["tace"]', 'Segmentation' => ''])
      ->addSegmentationType(SegmentationTypeEnum::TACE);
  }

}
