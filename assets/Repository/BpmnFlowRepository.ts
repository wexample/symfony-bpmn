import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnFlow from '../Entity/BpmnFlow.js';

export default class BpmnFlowRepository extends AbstractApiRepository<BpmnFlow> {
  static getEntityType() {
    return BpmnFlow;
  }
}
