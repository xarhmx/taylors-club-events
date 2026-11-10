
<?php
// create class 
class SampleData {
//create a method that gives back array 
    public static function getClubs(): array {

        return [
            [
                "id" => 1,
                "name" => "Coding Club",
                "category" => "Technology"
            ],
            [
                "id" => 2,
                "name" => "Photography Society",
                "category" => "Arts"
            ],
            [
                "id" => 3,
                "name" => "Sports Club",
                "category" => "Sports"
            ]
        ];
    }
}
