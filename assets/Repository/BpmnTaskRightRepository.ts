import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnTaskRight from '../Entity/BpmnTaskRight.js';

export default class BpmnTaskRightRepository extends AbstractApiRepository<BpmnTaskRight> {
  static getEntityType() {
    return BpmnTaskRight;
  }
}
