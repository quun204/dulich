<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if(isset($_POST['get_property'])){
  $propertyId = (int)$_POST['get_property'];

  $propertyRes = select(
    "SELECT hp.*, uc.name AS host_name FROM host_properties hp INNER JOIN user_cred uc ON hp.user_id = uc.id WHERE hp.id = ? LIMIT 1",
    [$propertyId],
    'i'
  );

  if(!$propertyRes || mysqli_num_rows($propertyRes) === 0){
    echo json_encode(['status' => 'not_found']);
    exit;
  }

  $property = mysqli_fetch_assoc($propertyRes);

  $featuresIds = json_decode($property['features'], true);
  $facilitiesIds = json_decode($property['facilities'], true);

  $featuresIds = is_array($featuresIds) ? array_map('intval', $featuresIds) : [];
  $facilitiesIds = is_array($facilitiesIds) ? array_map('intval', $facilitiesIds) : [];

  $featureNames = [];
  if(!empty($featuresIds)){
    $placeholders = implode(',', array_fill(0, count($featuresIds), '?'));
    $types = str_repeat('i', count($featuresIds));
    $featureRes = select("SELECT name FROM features WHERE id IN ($placeholders)", $featuresIds, $types);
    while($row = mysqli_fetch_assoc($featureRes)){
      $featureNames[] = $row['name'];
    }
  }

  $facilityNames = [];
  if(!empty($facilitiesIds)){
    $placeholders = implode(',', array_fill(0, count($facilitiesIds), '?'));
    $types = str_repeat('i', count($facilitiesIds));
    $facilityRes = select("SELECT name FROM facilities WHERE id IN ($placeholders)", $facilitiesIds, $types);
    while($row = mysqli_fetch_assoc($facilityRes)){
      $facilityNames[] = $row['name'];
    }
  }

  echo json_encode([
    'status' => 'success',
    'property' => [
      'name' => $property['property_name'],
      'area' => $property['area'],
      'price' => $property['price'],
      'quantity' => $property['quantity'],
      'adult' => $property['adult'],
      'children' => $property['children'],
      'description' => $property['description'],
      'status' => $property['status'],
      'created_at' => $property['created_at'],
      'host_name' => $property['host_name']
    ],
    'features' => $featureNames,
    'facilities' => $facilityNames
  ]);
}
?>
