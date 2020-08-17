/**
 * @file mautic.customFields.js
 * Pushes site and Lift info to custom fields in Mautic
 * 
 */
(function (Drupal) {

  "use strict";

  /**
   * Adds data to these custom fields in Mautic:
   *  scenario, site_url, lift_account, lift_segments
   */
  Drupal.behaviors.mauticCustomFields = {
    attach: function (context) {
      // Add DF custom field data
      mt('send', 'pageview', {
        site_url: window.location.hostname
      },
        {
          onerror: function () {
            console.log("Mautic - error adding site URL custom field.");
          }
        }
      );

      // Wait until Lift has finished to add the Lift segments
      window.addEventListener('acquiaLiftStageCollection', function (e) {
        mt('send', 'pageview', {
          lift_account: AcquiaLift.account_id,
          lift_segments: acquiaLiftSegmentsToString()
        },
          {
            onerror: function () {
              console.log("Mautic - error adding custom field data from Lift.");
            }
          }
        );
      });  // End eventListener

    }
  };

}(Drupal));

/**
 * A helper function for preparing the Lift Segments object for a text field.
 */
function acquiaLiftSegmentsToString() {
  let liftSegmentsExist = typeof AcquiaLift.currentSegments === 'object';
  let liftSegments = '';
  if (liftSegmentsExist) {
    // Convert the Lift Segments from an object to a string of ids
    Object.values(AcquiaLift.currentSegments).forEach(value => {
      liftSegments += value.id + ',';
    });
    // Remove the last comma
    liftSegments = liftSegments.slice(0, -1);
  }

  return liftSegments;
}
