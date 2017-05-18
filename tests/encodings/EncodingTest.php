<?php

namespace Tests;
/**
 * Created by PhpStorm.
 * User: Caleb Falcione
 * Date: 5/9/2017
 * Time: 6:37 PM
 */

use Encodings\MasterEncoding as MasterEncoding;
use Encodings\Assignment as Assignment;

class EncodingTest  extends BaseTestCase {

    public function setUp () {
        $this->assignments = json_decode($this->assignments_json);
        $this->exMasterEncoding = json_decode($this->exMasterEncoding_json);
    }

    public function testSetUp() {
        parent::assertTrue(is_array($this->assignments));
    }

    public function testAssignmentCreate() {
        //by extension, also tests the constructors for Encoding and ValueResponse
        foreach ($this->assignments as $assignment) {
            $assignment = new Assignment($assignment);
            $jsonOutput = json_encode($assignment);
            parent::assertNotTrue(is_bool($jsonOutput));
//            echo $jsonOutput;
        }
    }

    public function testMasterEncodingMerge () {
        $masterEncoding = new MasterEncoding();
        foreach ($this->assignments as $assignment) {
            $masterEncoding->merge($assignment);
        }
//        echo json_encode($masterEncoding);
        parent::assertTrue(json_encode($masterEncoding) === $this->exMasterEncoding_json);
    }

    public function testMasterEncodingInit() {
        $masterEncoding = new MasterEncoding($this->exMasterEncoding);
        parent::assertTrue(json_encode($masterEncoding) === $this->exMasterEncoding_json);
    }

    public function testMergeOnImportedMasterEncoding () {
        $masterEncoding_first = new MasterEncoding();
        for ($i = 0; $i < count($this->assignments) - 1; $i++) {
            $assignment = new Assignment($this->assignments[$i]);
            $masterEncoding_first->merge($assignment);
        }
        $json_first = json_encode($masterEncoding_first);
        $masterEncoding_second = new MasterEncoding($json_first);
        $masterEncoding_second->merge(end($this->assignments));
//        echo json_encode($masterEncoding_second);
        parent::assertTrue(json_encode($masterEncoding_second) === $this->exMasterEncoding_json);

    }


    protected $examplePaperPMCID = 3337602;

    protected $assignments_json = '[{"_key":"352","_id":"assignments/352","_rev":"_U3LmDG6--H","encoding":{"branches":[[]],"constants":[{"data":{"value":"prior to the experiment, mice were provided food and water ad libitum for three days"},"question":"acclimationPeriod"},{"data":{"value":0},"question":"acclimationDuration"},{"data":{"value":70},"question":"ageAtStart"},{"data":{},"question":"ageAtWeight"},{"data":{"value":"The Jackson Labaratory"},"question":"facilityName"},{"data":{"value":"Bar Harbor, ME"},"question":"facilityCityState"},{"data":{"value":"United States"},"question":"facilityCountry"},{"data":{"value":"field.falseOption"},"question":"animalLocations"},{"data":{"value":"Not germ free"},"question":"pathogenFreeEnvironment"},{"data":{"value":"Other","disabled":true},"question":"cageType"},{"data":{"value":"","disabled":true},"question":"airCirculation"},{"data":{"value":"","disabled":true},"question":"beddingMaterial"},{"data":{"value":"","disabled":true},"question":"changeFrequency"},{"data":{"value":"","disabled":true},"question":"enrichmentType"},{"data":{"value":"field.trueOption"},"question":"lightingSchedule"},{"data":{"value":"","disabled":true},"question":"lightHours"},{"data":{"value":"","disabled":true},"question":"darkHours"},{"data":{"value":"","disabled":true},"question":"lightStartTime"},{"data":{"value":"","disabled":true},"question":"facilityHumidity"},{"data":{"value":"","disabled":true},"question":"constantTemperature"},{"data":{"value":"","disabled":true},"question":"temperatureRange"},{"data":{"value":"","disabled":true},"question":"dietType"},{"data":{"value":"LabDiet 5001"},"question":"dietID"},{"data":{"value":"Purina Mills"},"question":"dietVendor"},{"data":{"value":"Gray Summit, MO"},"question":"dietVendorCity"},{"data":{"value":"Ad libitum"},"question":"feedingFrequency"},{"data":{"value":"","disabled":true},"question":"percentEnergy"},{"data":{"value":"","disabled":true},"question":"percentFat"},{"data":{"value":"","disabled":true},"question":"percentCarbohydrates"},{"data":{"value":"","disabled":true},"question":"percentProtein"},{"data":{"value":"","disabled":true},"question":"exerciseType"},{"data":{"value":"","disabled":true},"question":"exerciseFreq"},{"data":{"value":"","disabled":true},"question":"forcedExcecise"},{"data":{"value":28},"question":"daysOnTreatment"},{"data":{"value":"The Jackson Laboratory"},"question":"vendorName"},{"data":{"value":"","disabled":true},"question":"miceVendorCity"},{"data":{"value":"","disabled":true},"question":"vendorCountry"},{"data":{"value":"Male","disabled":true},"question":"sex"},{"data":{"value":"C57BL/6J"},"question":"breed"},{"data":{"value":"","disabled":true},"question":"surgeryType"},{"data":{"value":"","disabled":true},"question":"routeOfAdministration"},{"data":{"value":"","disabled":true},"question":"compoundName"},{"data":{"value":"","disabled":true},"question":"compoundFrequency"},{"data":{"value":"","disabled":true},"question":"dosage"},{"data":{"value":"","disabled":true},"question":"geneticManipulationType"},{"data":{"value":"field.trueOption"},"question":"ethicalStatement"},{"data":{"value":2},"question":"micePerCage"},{"data":{"value":"","disabled":true},"question":"sampleSize"},{"data":{"value":"","disabled":true},"question":"whereReported"},{"data":{"value":"","disabled":true},"question":"averageFinalWeight"},{"data":{"value":"","disabled":true},"question":"errorOfMeasurmentValue"},{"data":{"value":"","disabled":true},"question":"errorOfMeasurmentType"}]},"date_created":"2017-02-27 08:37:03","status":"active","completion":"100"},{"_key":"782","_id":"assignments/782","_rev":"_U3LmDLK--Q","encoding":{"branches":[[]],"constants":[{"data":{"value":""},"question":"acclimationPeriod"},{"data":{"value":""},"question":"acclimationDuration"},{"data":{"value":10},"question":"ageAtStart"},{"data":{"value":10},"question":"ageAtWeight"},{"data":{"value":"The Jackson Laboratory"},"question":"facilityName"},{"data":{"value":"Bar Harbor,ME"},"question":"facilityCityState"},{"data":{"value":"United States"},"question":"facilityCountry"},{"data":{"value":"field.falseOption"},"question":"animalLocations"},{"data":{"value":"Completely germ-free"},"question":"pathogenFreeEnvironment"},{"data":{"value":"Open cages"},"question":"cageType"},{"data":{"value":""},"question":"airCirculation"},{"data":{"value":""},"question":"beddingMaterial"},{"data":{"value":""},"question":"changeFrequency"},{"data":{"value":""},"question":"enrichmentType"},{"data":{"value":""},"question":"lightingSchedule"},{"data":{"value":""},"question":"lightHours"},{"data":{"value":""},"question":"darkHours"},{"data":{"value":""},"question":"lightStartTime"},{"data":{"value":""},"question":"facilityHumidity"},{"data":{"value":""},"question":"constantTemperature"},{"data":{"value":""},"question":"temperatureRange"},{"data":{"value":""},"question":"dietType"},{"data":{"value":"5001"},"question":"dietID"},{"data":{"value":"LabDiet"},"question":"dietVendor"},{"data":{"value":"Purina Mills,Gray Summit,MO"},"question":"dietVendorCity"},{"data":{"value":"Ad libitum"},"question":"feedingFrequency"},{"data":{"value":""},"question":"percentEnergy"},{"data":{"value":""},"question":"percentFat"},{"data":{"value":""},"question":"percentCarbohydrates"},{"data":{"value":""},"question":"percentProtein"},{"data":{"value":""},"question":"exerciseType"},{"data":{"value":""},"question":"exerciseFreq"},{"data":{"value":""},"question":"forcedExcecise"},{"data":{"value":""},"question":"daysOnTreatment"},{"data":{"value":""},"question":"vendorName"},{"data":{"value":""},"question":"miceVendorCity"},{"data":{"value":""},"question":"vendorCountry"},{"data":{"value":""},"question":"sex"},{"data":{"value":""},"question":"breed"},{"data":{"value":""},"question":"surgeryType"},{"data":{"value":""},"question":"routeOfAdministration"},{"data":{"value":""},"question":"compoundName"},{"data":{"value":""},"question":"compoundFrequency"},{"data":{"value":""},"question":"dosage"},{"data":{"value":""},"question":"geneticManipulationType"},{"data":{"value":""},"question":"ethicalStatement"},{"data":{"value":""},"question":"micePerCage"},{"data":{"value":""},"question":"sampleSize"},{"data":{"value":""},"question":"whereReported"},{"data":{"value":""},"question":"averageFinalWeight"},{"data":{"value":""},"question":"errorOfMeasurmentValue"},{"data":{"value":""},"question":"errorOfMeasurmentType"}]},"date_created":"2017-03-06 10:43:37","status":"active","completion":"11"},{"_key":"281","_id":"assignments/281","_rev":"_U3LmDL---O","encoding":{"branches":[[]],"constants":[{"data":{"value":"After three days of cage acclimation, unanesthetized mice (4 per exposure) were irradiated (IR; JL Shepherd Mark I, 137Cs gamma ray source, San Gabriel, CA) with 1, 10, or 100â€‰cGy at 0.96, 0.96, or 88â€‰cGy/min (Â±10%), respectively, or were sham-irradiated (0â€‰cGy-controls)."},"question":"acclimationPeriod"},{"data":{"value":4},"question":"acclimationDuration"},{"data":{"value":3},"question":"ageAtStart"},{"data":{"value":5},"question":"ageAtWeight"},{"data":{"value":"","disabled":true},"question":"facilityName"},{"data":{"value":"Moffett fields, California and Irvine, California"},"question":"facilityCityState"},{"data":{"value":"United States"},"question":"facilityCountry"},{"data":{"value":"field.trueOption"},"question":"animalLocations"},{"data":{"value":"Completely germ-free"},"question":"pathogenFreeEnvironment"},{"data":{"value":"Open cages"},"question":"cageType"},{"data":{"value":"Open cages"},"question":"airCirculation"},{"data":{"value":"","disabled":true},"question":"beddingMaterial"},{"data":{"value":"","disabled":true},"question":"changeFrequency"},{"data":{"value":"","disabled":true},"question":"enrichmentType"},{"data":{"value":"field.falseOption"},"question":"lightingSchedule"},{"data":{"value":2},"question":"lightHours"},{"data":{"value":5},"question":"darkHours"},{"data":{"value":10},"question":"lightStartTime"},{"data":{"upperValue":75,"value":"","lowerValue":38},"question":"facilityHumidity"},{"data":{"value":"field.trueOption"},"question":"constantTemperature"},{"data":{"upperValue":46,"value":"","lowerValue":0},"question":"temperatureRange"},{"data":{"value":"Food and water ad lubitium"},"question":"dietType"},{"data":{"value":"C57BL/6J"},"question":"dietID"},{"data":{"value":"NASA"},"question":"dietVendor"},{"data":{"value":"Moffet Field, CA and Irvine, CA"},"question":"dietVendorCity"},{"data":{"value":"Ad libitum"},"question":"feedingFrequency"},{"data":{"value":"field.falseOption"},"question":"percentEnergy"},{"data":{"value":25},"question":"percentFat"},{"data":{"value":45},"question":"percentCarbohydrates"},{"data":{"value":30},"question":"percentProtein"},{"data":{"value":"","disabled":true},"question":"exerciseType"},{"data":{"value":"Weekly"},"question":"exerciseFreq"},{"data":{"value":"field.falseOption"},"question":"forcedExcecise"},{"data":{},"question":"daysOnTreatment"},{"data":{"value":"NASA"},"question":"vendorName"},{"data":{"value":"Moffett Field, CA and Irvine, CA"},"question":"miceVendorCity"},{"data":{"value":"United States"},"question":"vendorCountry"},{"data":{"value":"Male"},"question":"sex"},{"data":{"value":"C57BL/6J"},"question":"breed"},{"data":{"value":"Gastric bypass"},"question":"surgeryType"},{"data":{"value":"Food"},"question":"routeOfAdministration"},{"data":{"value":"Sucrose"},"question":"compoundName"},{"data":{"value":"Weekly"},"question":"compoundFrequency"},{"data":{"value":"200IU"},"question":"dosage"},{"data":{"value":"They were given osteoporosis"},"question":"geneticManipulationType"},{"data":{"value":"field.falseOption"},"question":"ethicalStatement"},{"data":{"value":2},"question":"micePerCage"},{"data":{"value":5},"question":"sampleSize"},{"data":{"value":"Text, with numbers reported"},"question":"whereReported"},{"data":{"value":30},"question":"averageFinalWeight"},{"data":{},"question":"errorOfMeasurmentValue"},{"data":{"value":"Standard Deviation (S.D. or s.d.)"},"question":"errorOfMeasurmentType"}]},"date_created":"2017-02-24 22:31:42","status":"active","completion":"100"},{"_key":"531","_id":"assignments/531","_rev":"_U3LmDKi--I","encoding":{"branches":[[]],"constants":[{"data":{"value":"Mice were provided food (LabDiet 5001, Purina Mills, Gray Summit, MO) and water ad libitum."},"question":"acclimationPeriod"},{"data":{"value":16},"question":"acclimationDuration"},{"data":{"value":10},"question":"ageAtStart"},{"data":{"value":27},"question":"ageAtWeight"},{"data":{"value":"The Jackson Laboratory"},"question":"facilityName"},{"data":{"value":"Bar Harbor, ME"},"question":"facilityCityState"},{"data":{"value":"USA"},"question":"facilityCountry"},{"data":{"value":"field.falseOption"},"question":"animalLocations"},{"data":{"value":"","disabled":true},"question":"pathogenFreeEnvironment"},{"data":{"value":"","disabled":true},"question":"cageType"},{"data":{"value":"","disabled":true},"question":"airCirculation"},{"data":{"value":"","disabled":true},"question":"beddingMaterial"},{"data":{"value":"","disabled":true},"question":"changeFrequency"},{"data":{"value":"","disabled":true},"question":"enrichmentType"},{"data":{"value":"","disabled":true},"question":"lightingSchedule"},{"data":{"value":"","disabled":true},"question":"lightHours"},{"data":{"value":"","disabled":true},"question":"darkHours"},{"data":{"value":"","disabled":true},"question":"lightStartTime"},{"data":{"value":"","disabled":true},"question":"facilityHumidity"},{"data":{"value":"","disabled":true},"question":"constantTemperature"},{"data":{"value":"","disabled":true},"question":"temperatureRange"},{"data":{"value":"Lab diet"},"question":"dietType"},{"data":{"value":"5001"},"question":"dietID"},{"data":{"value":"Purina Mills, Gray Summit, MO"},"question":"dietVendor"},{"data":{"value":"Gray Summit, MO"},"question":"dietVendorCity"},{"data":{"value":"","disabled":true},"question":"feedingFrequency"},{"data":{"value":"","disabled":true},"question":"percentEnergy"},{"data":{"value":"","disabled":true},"question":"percentFat"},{"data":{"value":"","disabled":true},"question":"percentCarbohydrates"},{"data":{"value":"","disabled":true},"question":"percentProtein"},{"data":{"value":"","disabled":true},"question":"exerciseType"},{"data":{"value":"","disabled":true},"question":"exerciseFreq"},{"data":{"value":"","disabled":true},"question":"forcedExcecise"},{"data":{"value":"","disabled":true},"question":"daysOnTreatment"},{"data":{"value":"","disabled":true},"question":"vendorName"},{"data":{"value":"","disabled":true},"question":"miceVendorCity"},{"data":{"value":"","disabled":true},"question":"vendorCountry"},{"data":{"value":"Male"},"question":"sex"},{"data":{"value":"C57BL/6J"},"question":"breed"},{"data":{"value":"Tissues were harvested on the day of IR (basal) or 1 or 4 months later (n = 8/group), with cell culture being performed at the latter two endpoints."},"question":"surgeryType"},{"data":{"value":"Topically (on the bodys surface"},"question":"routeOfAdministration"},{"data":{"value":"(IR; JL Shepherd Mark I, 137Cs"},"question":"compoundName"},{"data":{"value":"","disabled":true},"question":"compoundFrequency"},{"data":{"value":"","disabled":true},"question":"dosage"},{"data":{"value":"","disabled":true},"question":"geneticManipulationType"},{"data":{"value":"field.trueOption"},"question":"ethicalStatement"},{"data":{"value":2},"question":"micePerCage"},{"data":{"value":8},"question":"sampleSize"},{"data":{"value":"Text, with numbers reported"},"question":"whereReported"},{"data":{"value":30},"question":"averageFinalWeight"},{"data":{"value":10},"question":"errorOfMeasurmentValue"},{"data":{"value":"","disabled":true},"question":"errorOfMeasurmentType"}]},"date_created":"2017-03-06 08:17:29","status":"active","completion":"100"},{"_key":"363","_id":"assignments/363","_rev":"_U3LmDLK--O","encoding":{"branches":[[]],"constants":[{"data":{"value":"Mice were irradiated and tissues were harvested on the day of irradiation."},"question":"acclimationPeriod"},{"data":{"value":12},"question":"acclimationDuration"},{"data":{"value":70,"disabled":false},"question":"ageAtStart"},{"data":{"value":189},"question":"ageAtWeight"},{"data":{"value":"The Jackson Laboratory"},"question":"facilityName"},{"data":{"value":"Bar Harbor, ME"},"question":"facilityCityState"},{"data":{"value":"USA"},"question":"facilityCountry"},{"data":{"value":"field.falseOption"},"question":"animalLocations"},{"data":{"value":""},"question":"pathogenFreeEnvironment"},{"data":{"value":""},"question":"cageType"},{"data":{"value":""},"question":"airCirculation"},{"data":{"value":""},"question":"beddingMaterial"},{"data":{"value":""},"question":"changeFrequency"},{"data":{"value":""},"question":"enrichmentType"},{"data":{"value":""},"question":"lightingSchedule"},{"data":{"value":""},"question":"lightHours"},{"data":{"value":""},"question":"darkHours"},{"data":{"value":""},"question":"lightStartTime"},{"data":{"value":""},"question":"facilityHumidity"},{"data":{"value":""},"question":"constantTemperature"},{"data":{"value":""},"question":"temperatureRange"},{"data":{"value":"LabDiet 5001, Purina Mills, Gray Summit, MO"},"question":"dietType"},{"data":{"value":"LabDiet 5001"},"question":"dietID"},{"data":{"value":"Purina Mills"},"question":"dietVendor"},{"data":{"value":"Gray Summit, MO"},"question":"dietVendorCity"},{"data":{"value":"Ad libitum"},"question":"feedingFrequency"},{"data":{"value":""},"question":"percentEnergy"},{"data":{"value":""},"question":"percentFat"},{"data":{"value":""},"question":"percentCarbohydrates"},{"data":{"value":""},"question":"percentProtein"},{"data":{"value":""},"question":"exerciseType"},{"data":{"value":""},"question":"exerciseFreq"},{"data":{"value":"field.falseOption"},"question":"forcedExcecise"},{"data":{"value":""},"question":"daysOnTreatment"},{"data":{"value":"The Jackson Laboratory"},"question":"vendorName"},{"data":{"value":"Bar Harbor, ME"},"question":"miceVendorCity"},{"data":{"value":"USA"},"question":"vendorCountry"},{"data":{"value":"Male"},"question":"sex"},{"data":{"value":""},"question":"breed"},{"data":{"value":""},"question":"surgeryType"},{"data":{"value":""},"question":"routeOfAdministration"},{"data":{"value":""},"question":"compoundName"},{"data":{"value":""},"question":"compoundFrequency"},{"data":{"value":""},"question":"dosage"},{"data":{"value":""},"question":"geneticManipulationType"},{"data":{"value":""},"question":"ethicalStatement"},{"data":{"value":4},"question":"micePerCage"},{"data":{"value":4},"question":"sampleSize"},{"data":{"value":"In a figure"},"question":"whereReported"},{"data":{},"question":"averageFinalWeight"},{"data":{"value":"","disabled":false},"question":"errorOfMeasurmentValue"},{"data":{"value":""},"question":"errorOfMeasurmentType"}]},"date_created":"2017-02-28 08:51:35","status":"active","completion":"100"}]';
    protected $assignments;

    protected $exMasterEncoding_json = '[{"varID":"acclimationPeriod","location":0,"responses":[{"data":{"value":"prior to the experiment, mice were provided food and water ad libitum for three days"},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"After three days of cage acclimation, unanesthetized mice (4 per exposure) were irradiated (IR; JL Shepherd Mark I, 137Cs gamma ray source, San Gabriel, CA) with 1, 10, or 100\u00e2\u20ac\u2030cGy at 0.96, 0.96, or 88\u00e2\u20ac\u2030cGy\/min (\u00c2\u00b110%), respectively, or were sham-irradiated (0\u00e2\u20ac\u2030cGy-controls)."},"users":["281"]},{"data":{"value":"Mice were provided food (LabDiet 5001, Purina Mills, Gray Summit, MO) and water ad libitum."},"users":["531"]},{"data":{"value":"Mice were irradiated and tissues were harvested on the day of irradiation."},"users":["363"]}]},{"varID":"acclimationDuration","location":0,"responses":[{"data":{"value":0},"users":["352","782"]},{"data":{"value":4},"users":["281"]},{"data":{"value":16},"users":["531"]},{"data":{"value":12},"users":["363"]}]},{"varID":"ageAtStart","location":0,"responses":[{"data":{"value":70},"users":["352"]},{"data":{"value":10},"users":["782","531"]},{"data":{"value":3},"users":["281"]},{"data":{"value":70,"disabled":false},"users":["363"]}]},{"varID":"ageAtWeight","location":0,"responses":[{"data":{},"users":["352"]},{"data":{"value":10},"users":["782"]},{"data":{"value":5},"users":["281"]},{"data":{"value":27},"users":["531"]},{"data":{"value":189},"users":["363"]}]},{"varID":"facilityName","location":0,"responses":[{"data":{"value":"The Jackson Labaratory"},"users":["352"]},{"data":{"value":"The Jackson Laboratory"},"users":["782","531","363"]},{"data":{"value":"","disabled":true},"users":["281"]}]},{"varID":"facilityCityState","location":0,"responses":[{"data":{"value":"Bar Harbor, ME"},"users":["352","531","363"]},{"data":{"value":"Bar Harbor,ME"},"users":["782"]},{"data":{"value":"Moffett fields, California and Irvine, California"},"users":["281"]}]},{"varID":"facilityCountry","location":0,"responses":[{"data":{"value":"United States"},"users":["352","782","281"]},{"data":{"value":"USA"},"users":["531","363"]}]},{"varID":"animalLocations","location":0,"responses":[{"data":{"value":"field.falseOption"},"users":["352","782","531","363"]},{"data":{"value":"field.trueOption"},"users":["281"]}]},{"varID":"pathogenFreeEnvironment","location":0,"responses":[{"data":{"value":"Not germ free"},"users":["352"]},{"data":{"value":"Completely germ-free"},"users":["782","281"]},{"data":{"value":"","disabled":true},"users":["531"]},{"data":{"value":""},"users":["363"]}]},{"varID":"cageType","location":0,"responses":[{"data":{"value":"Other","disabled":true},"users":["352"]},{"data":{"value":"Open cages"},"users":["782","281"]},{"data":{"value":"","disabled":true},"users":["531"]},{"data":{"value":""},"users":["363"]}]},{"varID":"airCirculation","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Open cages"},"users":["281"]}]},{"varID":"beddingMaterial","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","281","531"]},{"data":{"value":""},"users":["782","363"]}]},{"varID":"changeFrequency","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","281","531"]},{"data":{"value":""},"users":["782","363"]}]},{"varID":"enrichmentType","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","281","531"]},{"data":{"value":""},"users":["782","363"]}]},{"varID":"lightingSchedule","location":0,"responses":[{"data":{"value":"field.trueOption"},"users":["352"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"field.falseOption"},"users":["281"]},{"data":{"value":"","disabled":true},"users":["531"]}]},{"varID":"lightHours","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":2},"users":["281"]}]},{"varID":"darkHours","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":5},"users":["281"]}]},{"varID":"lightStartTime","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":10},"users":["281"]}]},{"varID":"facilityHumidity","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"upperValue":75,"value":"","lowerValue":38},"users":["281"]}]},{"varID":"constantTemperature","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"field.trueOption"},"users":["281"]}]},{"varID":"temperatureRange","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"upperValue":46,"value":"","lowerValue":0},"users":["281"]}]},{"varID":"dietType","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"Food and water ad lubitium"},"users":["281"]},{"data":{"value":"Lab diet"},"users":["531"]},{"data":{"value":"LabDiet 5001, Purina Mills, Gray Summit, MO"},"users":["363"]}]},{"varID":"dietID","location":0,"responses":[{"data":{"value":"LabDiet 5001"},"users":["352","363"]},{"data":{"value":"5001"},"users":["782","531"]},{"data":{"value":"C57BL\/6J"},"users":["281"]}]},{"varID":"dietVendor","location":0,"responses":[{"data":{"value":"Purina Mills"},"users":["352","363"]},{"data":{"value":"LabDiet"},"users":["782"]},{"data":{"value":"NASA"},"users":["281"]},{"data":{"value":"Purina Mills, Gray Summit, MO"},"users":["531"]}]},{"varID":"dietVendorCity","location":0,"responses":[{"data":{"value":"Gray Summit, MO"},"users":["352","531","363"]},{"data":{"value":"Purina Mills,Gray Summit,MO"},"users":["782"]},{"data":{"value":"Moffet Field, CA and Irvine, CA"},"users":["281"]}]},{"varID":"feedingFrequency","location":0,"responses":[{"data":{"value":"Ad libitum"},"users":["352","782","281","363"]},{"data":{"value":"","disabled":true},"users":["531"]}]},{"varID":"percentEnergy","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"field.falseOption"},"users":["281"]}]},{"varID":"percentFat","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":25},"users":["281"]}]},{"varID":"percentCarbohydrates","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":45},"users":["281"]}]},{"varID":"percentProtein","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":30},"users":["281"]}]},{"varID":"exerciseType","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","281","531"]},{"data":{"value":""},"users":["782","363"]}]},{"varID":"exerciseFreq","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Weekly"},"users":["281"]}]},{"varID":"forcedExcecise","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"field.falseOption"},"users":["281","363"]}]},{"varID":"daysOnTreatment","location":0,"responses":[{"data":{"value":28},"users":["352"]},{"data":{"value":""},"users":["782","363"]},{"data":{},"users":["281"]},{"data":{"value":"","disabled":true},"users":["531"]}]},{"varID":"vendorName","location":0,"responses":[{"data":{"value":"The Jackson Laboratory"},"users":["352","363"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"NASA"},"users":["281"]},{"data":{"value":"","disabled":true},"users":["531"]}]},{"varID":"miceVendorCity","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"Moffett Field, CA and Irvine, CA"},"users":["281"]},{"data":{"value":"Bar Harbor, ME"},"users":["363"]}]},{"varID":"vendorCountry","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"United States"},"users":["281"]},{"data":{"value":"USA"},"users":["363"]}]},{"varID":"sex","location":0,"responses":[{"data":{"value":"Male","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"Male"},"users":["281","531","363"]}]},{"varID":"breed","location":0,"responses":[{"data":{"value":"C57BL\/6J"},"users":["352","281","531"]},{"data":{"value":""},"users":["782","363"]}]},{"varID":"surgeryType","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Gastric bypass"},"users":["281"]},{"data":{"value":"Tissues were harvested on the day of IR (basal) or 1 or 4 months later (n = 8\/group), with cell culture being performed at the latter two endpoints."},"users":["531"]}]},{"varID":"routeOfAdministration","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Food"},"users":["281"]},{"data":{"value":"Topically (on the bodys surface"},"users":["531"]}]},{"varID":"compoundName","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Sucrose"},"users":["281"]},{"data":{"value":"(IR; JL Shepherd Mark I, 137Cs"},"users":["531"]}]},{"varID":"compoundFrequency","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Weekly"},"users":["281"]}]},{"varID":"dosage","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"200IU"},"users":["281"]}]},{"varID":"geneticManipulationType","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"They were given osteoporosis"},"users":["281"]}]},{"varID":"ethicalStatement","location":0,"responses":[{"data":{"value":"field.trueOption"},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"field.falseOption"},"users":["281"]}]},{"varID":"micePerCage","location":0,"responses":[{"data":{"value":2},"users":["352","281","531"]},{"data":{"value":""},"users":["782"]},{"data":{"value":4},"users":["363"]}]},{"varID":"sampleSize","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{"value":5},"users":["281"]},{"data":{"value":8},"users":["531"]},{"data":{"value":4},"users":["363"]}]},{"varID":"whereReported","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{"value":"Text, with numbers reported"},"users":["281","531"]},{"data":{"value":"In a figure"},"users":["363"]}]},{"varID":"averageFinalWeight","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{"value":30},"users":["281","531"]},{"data":{},"users":["363"]}]},{"varID":"errorOfMeasurmentValue","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352"]},{"data":{"value":""},"users":["782"]},{"data":{},"users":["281"]},{"data":{"value":10},"users":["531"]},{"data":{"value":"","disabled":false},"users":["363"]}]},{"varID":"errorOfMeasurmentType","location":0,"responses":[{"data":{"value":"","disabled":true},"users":["352","531"]},{"data":{"value":""},"users":["782","363"]},{"data":{"value":"Standard Deviation (S.D. or s.d.)"},"users":["281"]}]}]';
    protected $exMasterEncoding;
}