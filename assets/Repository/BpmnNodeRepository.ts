import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import BpmnNode from '../Entity/BpmnNode.js';

export default class BpmnNodeRepository extends AbstractApiRepository<BpmnNode> {
  static getEntityType() {
    return BpmnNode;
  }
}
