import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnLane from '../Entity/BpmnLane.js';

export default class BpmnLaneRepository extends AbstractApiRepository<BpmnLane> {
  static getEntityType() {
    return BpmnLane;
  }
}
