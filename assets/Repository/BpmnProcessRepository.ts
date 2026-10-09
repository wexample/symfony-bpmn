import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnProcess from '../Entity/BpmnProcess.js';

export default class BpmnProcessRepository extends AbstractApiRepository<BpmnProcess> {
  static getEntityType() {
    return BpmnProcess;
  }
}
